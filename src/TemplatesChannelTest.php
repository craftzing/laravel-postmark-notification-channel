<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Closure;
use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\MailRoutingNotifiable;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Config as ConfigFacade;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\TemplatesApi as TemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Generator;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Postmark\Models\PostmarkAttachment;

final class TemplatesChannelTest extends IntegrationTestCase
{
    private TemplatesChannel $channel;
    private Config $config;

    /**
     * @before
     */
    public function setupChannel(): void
    {
        $this->afterApplicationCreated(function (): void {
            TemplatesApi::fake();
            $this->channel = $this->app[TemplatesChannel::class];
            $this->config = $this->app[Config::class];
        });
    }

    /**
     * @after
     */
    public function unsetChannel(): void
    {
        unset($this->channel, $this->config);
    }

    public function channelConfigurations(): Generator
    {
        yield 'Send via Templates API' => [
            function (): void {
                // This is the default configuration...
            },
        ];

        yield 'Send via mail channel' => [
            function (): void {
                ConfigFacade::enableSendingViaMailChannel();
            },
        ];
    }

    /**
     * @test
     * @dataProvider channelConfigurations
     * @param callable(): void $configureChannel
     */
    public function itFailsWhenSendingNotificationsThatCannotBeConvertedToAPostmarkTemplate(
        callable $configureChannel
    ): void {
        $configureChannel();
        $notifiable = new MailRoutingNotifiable();
        $notification = new Notification();

        $this->expectExceptionObject(
            CannotConvertNotificationToPostmarkTemplate::missingToPostmarkTemplateMethod($notification),
        );

        $this->channel->send($notifiable, $notification);
    }

    /**
     * @test
     * @dataProvider channelConfigurations
     * @param callable(): void $configureChannel
     */
    public function itFailsWhenTheTemplatesApiCouldNotSendTheNotification(callable $configureChannel): void
    {
        $configureChannel();
        $e = TemplatesApi::failToSend();
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification();

        $this->expectExceptionObject($e);

        $this->channel->send($notifiable, $notification);
    }

    public function templateMessages(): Generator
    {
        yield 'From the default sender to the notifiable' => [
            new TemplateNotification(),
            fn (TemplateMessage $message, MailRoutingNotifiable $notifiable, Sender $defaultSender) => $message
                ->from($defaultSender)
                ->to(Recipients::fromEmails($notifiable->email)),
        ];

        yield 'From the default sender to predefined recipients' => [
            new TemplateNotification(
                (new TemplateMessage(TemplateAlias::fromAlias('welcome'), DynamicTemplateModel::fromAttributes([])))
                    ->to(Recipients::fromEmails($this->faker()->email)),
            ),
            fn (TemplateMessage $message, MailRoutingNotifiable $notifiable, Sender $defaultSender) => $message
                ->from($defaultSender),
        ];

        yield 'From a predefined sender to the notifiable' => [
            new TemplateNotification(
                (new TemplateMessage(TemplateAlias::fromAlias('welcome'), DynamicTemplateModel::fromAttributes([])))
                    ->from(Sender::fromEmail($this->faker()->email)),
            ),
            fn (TemplateMessage $message, MailRoutingNotifiable $notifiable, Sender $defaultSender) => $message
                ->to(Recipients::fromEmails($notifiable->email)),
        ];

        yield 'From a predefined sender to predefined recipients' => [
            new TemplateNotification(
                (new TemplateMessage(TemplateAlias::fromAlias('welcome'), DynamicTemplateModel::fromAttributes([])))
                    ->from(Sender::fromEmail($this->faker()->email))
                    ->to(Recipients::fromEmails($this->faker()->email)),
            ),
            fn (TemplateMessage $message, MailRoutingNotifiable $notifiable, Sender $defaultSender) => $message,
        ];

        yield 'With all options' => [
            new TemplateNotification(
                (new TemplateMessage(
                    TemplateAlias::fromAlias('welcome'),
                    DynamicTemplateModel::fromAttributes(['foo' => 'bar']),
                ))
                    ->from(Sender::fromEmail($this->faker()->email))
                    ->to(Recipients::fromEmails($this->faker()->email))
                    ->bcc(Recipients::fromEmails($this->faker()->email))
                    ->headers(['header' => 'value'])
                    ->attachments($this->createMock(PostmarkAttachment::class))
                    ->trackOpens()
                    ->trackLinks(TrackLinks::HTML_AND_TEXT())
                    ->tag('test')
                    ->metadata(['meta' => 'value'])
                    ->messageStream('outgoing'),
            ),
            fn (TemplateMessage $message) => $message,
        ];
    }

    /**
     * @test
     * @dataProvider templateMessages
     * @param callable(TemplateMessage, MailRoutingNotifiable, Sender): TemplateMessage $expectMessage
     */
    public function itCanSendEmailTemplateMessagesViaTheTemplatesApi(
        TemplateNotification $notification,
        callable $expectMessage
    ): void {
        $notifiable = new MailRoutingNotifiable();
        $expectedMessage = $expectMessage(
            $notification->toPostmarkTemplate(),
            $notifiable,
            $this->config->defaultSender(),
        );

        $this->channel->send($notifiable, $notification);

        TemplatesApi::assertSent($expectedMessage);
        TemplatesApi::assertNothingValidated();
        Mail::assertNothingSent();
    }

    /**
     * @test
     */
    public function itFailsWhenTheTemplatesApiFailedToValidateTheTemplateWhileSendingViaTheMailChannel(): void
    {
        ConfigFacade::enableSendingViaMailChannel();
        $e = TemplatesApi::failToValidate();
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification();

        $this->expectExceptionObject($e);

        $this->channel->send($notifiable, $notification);
    }

    /**
     * @test
     */
    public function itFailsWhenTheTemplateContentIsNotParseableWhileSendingViaTheMailChannel(): void
    {
        ConfigFacade::enableSendingViaMailChannel();
        $validatedTemplateMessage = TemplatesApi::respondWithNonParseableTemplateContent();
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification();

        $this->expectExceptionObject(
            CouldNotSendNotification::templateContentIsNotParseable($validatedTemplateMessage),
        );

        $this->channel->send($notifiable, $notification);
    }

    /**
     * @test
     */
    public function itFailsWhenTheTemplateMessageIsInvalidWhileSendingViaTheMailChannel(): void
    {
        ConfigFacade::enableSendingViaMailChannel();
        $validatedTemplateMessage = TemplatesApi::respondWithInvalidTemplateMessage();
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification();

        $this->expectExceptionObject(
            CouldNotSendNotification::templateMessageIsInvalid($validatedTemplateMessage),
        );

        $this->channel->send($notifiable, $notification);
    }

    /**
     * @test
     * @dataProvider templateMessages
     * @param callable(TemplateMessage, MailRoutingNotifiable, Sender): TemplateMessage $expectMessage
     */
    public function itCanSendEmailTemplateMessagesViaTheMailChannel(
        TemplateNotification $notification,
        callable $expectMessage
    ): void {
        ConfigFacade::enableSendingViaMailChannel();
        $notifiable = new MailRoutingNotifiable();
        $expectedMessage = $expectMessage(
            $notification->toPostmarkTemplate(),
            $notifiable,
            $this->config->defaultSender(),
        );

        $this->channel->send($notifiable, $notification);

        TemplatesApi::assertValidated($expectedMessage);
        TemplatesApi::assertNothingSent();
        Mail::assertSent(RenderedEmailTemplateMail::class, function (RenderedEmailTemplateMail $mail) use (
            $notifiable,
            $expectedMessage
        ): bool {
            $this->assertTrue($mail->hasTo((string) $expectedMessage->recipients));

            if ($expectedMessage->bcc) {
                $this->assertTrue($mail->hasBcc((string) $expectedMessage->bcc));
            }

            $this->assertSame(
                FakeTemplatesApi::RENDERED_TEMPLATE['Subject']['RenderedContent'],
                $mail->subject,
            );
            $this->assertSame(
                FakeTemplatesApi::RENDERED_TEMPLATE['HtmlBody']['RenderedContent'],
                Closure::bind(fn () => $mail->html, null, RenderedEmailTemplateMail::class)(),
            );
            $this->assertSame(
                FakeTemplatesApi::RENDERED_TEMPLATE['TextBody']['RenderedContent'],
                $mail->textView,
            );

            return true;
        });
    }
}
