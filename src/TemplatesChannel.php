<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Notifications\Notification;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

use function method_exists;

final class TemplatesChannel
{
    private Config $config;
    private Mailer $mailer;
    private PostmarkClient $postmark;

    public function __construct(Config $config, Mailer $mailer, ?PostmarkClient $postmark = null)
    {
        $this->config = $config;
        $this->mailer = $mailer;
        $this->postmark = $postmark ?: $this->createPostmarkClient($config);
    }

    private function createPostmarkClient(Config $config): PostmarkClient
    {
        $postmark = new PostmarkClient($config->postmarkToken());

        if ($baseUri = $config->postmarkBaseUri()) {
            $postmark::$BASE_URL = $baseUri;
        }

        return $postmark;
    }

    /**
     * @param mixed $notifiable
     */
    public function send($notifiable, Notification $notification): void
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
            if ($e->postmarkApiErrorCode === PostmarkErrorCodes::RECIPIENT_IS_INACTIVE) {
                throw CouldNotSendNotification::recipientIsInactive($e);
            }

            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        }
    }

    private function sendViaMailChannel(TemplateMessage $message): void
    {
        try {
            $template = $this->postmark->getTemplate($message->identifier->get());
            $response = $this->postmark->validateTemplate(
                $template['Subject'],
                $template['HtmlBody'],
                $template['TextBody'],
                $message->model->attributes(),
                $message->inlineCss,
                $template['TemplateType'],
                $template['LayoutTemplate'],
            );
        } catch (PostmarkException $e) {
            if ($e->postmarkApiErrorCode === PostmarkErrorCodes::TEMPLATE_ID_INVALID_OR_NOT_FOUND) {
                throw CouldNotSendNotification::templateIdIsInvalidOrNotFound($e);
            }

            if ($e->postmarkApiErrorCode === PostmarkErrorCodes::INVALID_TEMPLATE_MODEL) {
                throw CouldNotSendNotification::invalidTemplateModel($e);
            }

            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        }

        if (! $response['AllContentIsValid']) {
            throw CouldNotSendNotification::templateContentIsInvalid();
        }

        $this->mailer
            ->to((string) $message->recipients)
            ->bcc((string) $message->bcc)
            ->send(RenderedEmailTemplate::fromRenderedContent(
                $template['Subject'],
                (string) $response['HtmlBody']['RenderedContent'],
                (string) $response['TextBody']['RenderedContent'],
            ));
    }

    private function convertNotificationToMessage(Notification $notification, $notifiable): TemplateMessage
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

    private function recipientFromNotifiable($notifiable, ?Notification $notification = null): Recipients
    {
        $emailAddress = $notifiable->routeNotificationFor('mail', $notification);

        return Recipients::fromEmails($emailAddress);
    }
}
