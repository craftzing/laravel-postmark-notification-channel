<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotValidateNotification;
use Postmark\Models\DynamicResponseModel;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

final class SdkTemplatesApi implements TemplatesApi
{
    private PostmarkClient $postmark;

    public function __construct(Config $config, ?PostmarkClient $postmark = null)
    {
        $this->postmark = $postmark ?: new PostmarkClient($config->postmarkToken());
    }

    public function send(TemplateMessage $message): void
    {
        try {
            $this->postmark->sendEmailWithTemplate(
                $message->sender->toString(),
                $message->recipients->toString(),
                $message->identifier->get(),
                $message->model->variables(),
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
            throw CouldNotSendNotification::requestToPostmarkApiFailed($e);
        }
    }

    public function validate(TemplateMessage $message): ValidatedTemplateMessage
    {
        try {
            // When using the validation endpoint of the Postmark Templates API, we must
            // provide the template in order to validate a Template model against it...
            $template = $this->postmark->getTemplate($message->identifier->get());

            // Next, we should pass the retrieved template along with the provided Template model to the
            // validation endpoint in order to get the template back with the provided model filled in...
            $response = $this->validateTemplate($template, $message->model->variables(), $message->inlineCss);

            // The endpoint handles a very loose validation policy. For any missing variables or attributes, it injects
            // a suggested Template model. And if you provide an empty array for a variable that requires attributes,
            // Postmark will just accept the empty array. Therefore, we hit the validation endpoint once again with
            // an empty Template model. This allows us to retrieve the complete suggested model, so we can validate
            // the provided Template model against it using our own implementation, which is much less forgiving.
            $suggestedTemplate = $this->validateTemplate($template, [], $message->inlineCss);
        } catch (PostmarkException $e) {
            throw CouldNotValidateNotification::requestToPostmarkApiFailed($e);
        }

        return ValidatedTemplateMessage::validate(
            $response,
            $message->model,
            $suggestedTemplate['SuggestedTemplateModel'] ?? [],
        );
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
}
