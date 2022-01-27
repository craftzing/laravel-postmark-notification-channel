<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Closure;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\MailRoutingNotifiable;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Config as ConfigFacade;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Generator;
use Illuminate\Support\Facades\Mail;

use function config;

final class TemplatesChannelE2ETest extends IntegrationTestCase
{
    private TemplatesChannel $channel;

    /**
     * @before
     */
    public function setupChannel(): void
    {
        $this->afterApplicationCreated(function (): void {
            ConfigFacade::dontFake();
            Postmark::dontFake();

            $this->channel = $this->app[TemplatesChannel::class];
        });
    }

    /**
     * @after
     */
    public function unsetChannel(): void
    {
        unset($this->config, $this->channel);
    }

    public function invalidTemplateMessages(): Generator
    {
        yield 'Template does not exist' => [
            new TemplateMessage(
                TemplateAlias::fromAlias('nonsense'),
                DynamicTemplateModel::fromAttributes(['name' => 'foo']),
            ),
        ];

// TODO: Prevent models with missing attributes from being validated
//        yield 'Template model has no attributes' => [
//            new TemplateMessage(
//                TemplateAlias::fromAlias('ci-template'),
//                DynamicTemplateModel::fromAttributes([]),
//            ),
//        ];
    }

    /**
     * @test
     * @dataProvider invalidTemplateMessages
     */
    public function itCannotSendViaTheMailChannelWhenTheMessageIsInvalid(TemplateMessage $message): void
    {
        config(['postmark-notification-channel.send_via_mail_channel' => true]);
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification($message);

        $this->expectException(CouldNotSendNotification::class);

        $this->channel->send($notifiable, $notification);

        Mail::assertNothingOutgoing();
    }

    /**
     * @test
     */
    public function itCanAnEmailTemplateSendViaTheMailChannel(): void
    {
        config(['postmark-notification-channel.send_via_mail_channel' => true]);
        $notifiable = new MailRoutingNotifiable();
        $bcc = Recipients::fromEmails('fake@craftzing.com');
        $message = (new TemplateMessage(
            TemplateAlias::fromAlias('ci-template'),
            DynamicTemplateModel::fromAttributes(['name' => 'foo']),
        ))->bcc($bcc);

        $this->channel->send($notifiable, new TemplateNotification($message));

        Mail::assertSent(RenderedEmailTemplate::class, function (RenderedEmailTemplate $mail) use (
            $notifiable,
            $bcc
        ): bool {
            $this->assertTrue($mail->hasTo($notifiable->email));
            $this->assertTrue($mail->hasBcc((string) $bcc));
            $this->assertSame('CI template', $mail->subject);
            $this->assertNotEmpty(Closure::bind(fn () => $mail->html, null, RenderedEmailTemplate::class)());
            $this->assertNotEmpty($mail->textView);

            return true;
        });
    }
}
