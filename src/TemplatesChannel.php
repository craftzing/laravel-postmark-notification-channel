<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Illuminate\Notifications\Notification;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

use function method_exists;

final class TemplatesChannel
{
    private PostmarkClient $postmark;
    private Sender $defaultSender;

    public function __construct(PostmarkClient $postmark, Config $config)
    {
        $this->postmark = $postmark;
        $this->defaultSender = $config->defaultSender();
    }

    /**
     * @param mixed $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        $message = $this->convertNotificationToMessage($notification, $notifiable);

        try {
            $this->postmark->sendEmailWithTemplate(
                $message->sender->toString(),
                $message->recipients->toString(),
                $message->identifier->get(),
                $message->model->attributes(),
                $message->inlineCss,
                $message->tag,
                $message->trackOpens,
                null,
                null,
                ((string) $message->bcc) ?: null,
                $message->headers,
                $message->attachments,
                ((string) $message->trackLinks) ?: null,
                $message->metadata,
                $message->messageStream,
            );
        } catch (PostmarkException $e) {
            if ($e->postmarkApiErrorCode === CouldNotSendNotification::POSTMARK_API_ERROR_CODE_RECIPIENT_IS_INACTIVE) {
                throw CouldNotSendNotification::recipientIsInactive($e);
            }

            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        }
    }

    private function convertNotificationToMessage(Notification $notification, $notifiable): TemplateMessage
    {
        if (! method_exists($notification, 'toPostmarkTemplate')) {
            throw CannotConvertNotificationToPostmarkTemplate::missingToPostmarkTemplateMethod($notification);
        }

        $message = $notification->toPostmarkTemplate($notifiable);

        if (! $message->sender) {
            $message = $message->from($this->defaultSender);
        }

        if (! $message->recipients) {
            $message = $message->to($this->recipientFromNotifiable($notifiable, $notification));
        }

        return $message;
    }

    private function recipientFromNotifiable($notifiable, ?Notification $notification = null): Recipients
    {
        $emailAddress = $notifiable->routeNotificationFor('mail', $notification);

        return Recipients::fromEmails($emailAddress);
    }
}
