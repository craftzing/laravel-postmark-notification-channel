<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CannotConvertNotificationToPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Notifications\Notification;
use Postmark\Models\DynamicResponseModel;
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

            // When validating a template, Postmark is very loose on which data is provided by the TemplateModel. For
            // any variable that is missing, it injects a "suggested" template model. And if you provide an empty
            // array for a variable that requires sub properties, Postmark will just accept the empty array.
            // Therefore, we should validate the Template once with the actual model and once with a
            // clearly invalid model in order to compare the "suggested" model with the actual one.
            $response = $this->validateTemplate($template, $message->model->attributes(), $message->inlineCss);
            $suggestedTemplateModel = $this->validateTemplate(
                $template,
                [],
                $message->inlineCss,
            )['SuggestedTemplateModel'] ?? [];
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

        $validatedModel = ValidatedTemplateModel::validate($message->model, $suggestedTemplateModel);

        if ($validatedModel->isIncompleteOrInvalid()) {
            throw CouldNotSendNotification::templateModelIsIncompleteOrInvalid($validatedModel);
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

    /**
     * @param array<mixed> $model
     */
    private function validateTemplate(
        DynamicResponseModel $template,
        array $model,
        bool $inlineCss
    ): DynamicResponseModel {
        return $this->postmark->validateTemplate(
            $template['Subject'],
            $template['HtmlBody'],
            $template['TextBody'],
            (object) $model,
            $inlineCss,
            $template['TemplateType'],
            $template['LayoutTemplate'],
        );
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
