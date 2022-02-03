<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\RequestToPostmarkTemplatesApiFailed;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\TemplateContentIsNotParseable;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Notifications\Notification;

use function method_exists;

final class TemplatesChannel
{
    private Config $config;
    private Mailer $mailer;
    private TemplatesApi $templatesApi;

    public function __construct(Config $config, Mailer $mailer, TemplatesApi $templatesApi)
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->templatesApi = $templatesApi;
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $message = $this->convertNotificationToMessage($notification, $notifiable);

        if ($this->config->shouldSendViaMailChannel()) {
            $this->sendViaMailChannel($message);
        } else {
            $this->sendViaTemplatesApi($message);
        }
    }

    private function sendViaTemplatesApi(TemplateMessage $message): void
    {
        try {
            $this->templatesApi->send($message);
        } catch (RequestToPostmarkTemplatesApiFailed $e) {
            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        }
    }

    private function sendViaMailChannel(TemplateMessage $message): void
    {
        try {
            $validatedMessage = $this->templatesApi->validate($message);
        } catch (RequestToPostmarkTemplatesApiFailed $e) {
            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        } catch (TemplateContentIsNotParseable $e) {
            throw CouldNotSendNotification::templateContentIsNotParseable($e);
        }

        if ($validatedMessage->isInvalid()) {
            throw CouldNotSendNotification::templateMessageIsInvalid($validatedMessage);
        }

        $this->mailer
            ->to((string) $message->recipients)
            ->bcc((string) $message->bcc)
            ->send(RenderedEmailTemplateMail::fromRenderedContent(
                $validatedMessage->subject,
                $validatedMessage->htmlBody,
                $validatedMessage->textBody,
            ));
    }

    private function convertNotificationToMessage(Notification $notification, object $notifiable): TemplateMessage
    {
        if (! method_exists($notification, 'toPostmarkTemplate')) {
            throw CannotConvertNotificationToPostmarkTemplate::missingToPostmarkTemplateMethod($notification);
        }

        $message = $notification->toPostmarkTemplate($notifiable);

        if (! $message->sender) {
            $message = $message->from($this->config->defaultSender());
        }

        if (! $message->recipients) {
            $message = $message->to($this->recipientFromNotifiable($notifiable, $notification));
        }

        return $message;
    }

    private function recipientFromNotifiable(object $notifiable, ?Notification $notification = null): Recipients
    {
        $emailAddress = $notifiable->routeNotificationFor('mail', $notification);

        return Recipients::fromEmails($emailAddress);
    }
}
