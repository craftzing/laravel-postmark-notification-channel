<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\MailRoutingNotifiable;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\TemplatesApi as TemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Generator;
use Illuminate\Notifications\Notification;
use Postmark\Models\PostmarkAttachment;

final class TemplatesChannelTest extends IntegrationTestCase
{
    private Config $config;
    private TemplatesChannel $channel;

    /**
     * @before
     */
    public function setupChannel(): void
    {
        $this->afterApplicationCreated(function (): void {
            TemplatesApi::fake();
            $this->config = $this->app[Config::class];
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

    /**
     * @test
     */
    public function itFailsWhenSendingNotificationsThatCannotBeConvertedToAPostmarkTemplate(): void
    {
        $notifiable = new MailRoutingNotifiable();
        $notification = new Notification();

        $this->expectExceptionObject(
            CannotConvertNotificationToPostmarkTemplate::missingToPostmarkTemplateMethod($notification),
        );

        $this->channel->send($notifiable, $notification);
    }

    /**
     * @test
     */
    public function itFailsWhenTheRequestToPostmarkTemplatesApiFailed(): void
    {
        $notifiable = new MailRoutingNotifiable();
        $notification = new TemplateNotification();
        $e = TemplatesApi::failRequestToPostmark();

        $this->expectExceptionObject(CouldNotSendNotification::requestToPostmarkApiFailed($e));

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
     * @param callable(TemplateMessage, MailRoutingNotifiable, Sender): TemplateMessage $resolveExpectedMessage
     */
    public function itCanSendEmailTemplateMessagesViaThePostmarkAPI(
        TemplateNotification $notification,
        callable $resolveExpectedMessage
    ): void {
        $notifiable = new MailRoutingNotifiable();
        $expectedMessage = $resolveExpectedMessage(
            $notification->toPostmarkTemplate(),
            $notifiable,
            $this->config->defaultSender(),
        );

        $this->channel->send($notifiable, $notification);

        TemplatesApi::assertSent($expectedMessage);
    }
}
