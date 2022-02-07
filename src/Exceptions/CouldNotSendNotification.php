<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Craftzing\Laravel\NotificationChannels\Postmark\ValidatedTemplateMessage;
use Exception;
use Postmark\Models\PostmarkException;

use function json_encode;

use const JSON_PRETTY_PRINT;

final class CouldNotSendNotification extends Exception
{
    public static function requestToPostmarkApiFailed(PostmarkException $e): self
    {
        return new self(
            'The request to the Postmark failed while trying to send the notification: ' .
            "`$e->httpStatusCode - $e->message (API error code: $e->postmarkApiErrorCode)`",
        );
    }

    public static function templateContentIsNotParseable(ValidatedTemplateMessage $message): self
    {
        return new self(
            'Postmark could not parse the provided `Subject`, `HtmlBody` or `TextBody`: \n' .
            'Subject: ' . json_encode($message->subject, JSON_PRETTY_PRINT) .
            'HtmlBody: ' . json_encode($message->htmlBody, JSON_PRETTY_PRINT) .
            'TextBody: ' . json_encode($message->textBody, JSON_PRETTY_PRINT)
        );
    }

    public static function templateMessageIsInvalid(ValidatedTemplateMessage $message): self
    {
        return new self(
            'The Template model is invalid. Make sure to adhere to the suggested template model: \n\n' .
            'MISSING: ' . json_encode($message->missingVariables, JSON_PRETTY_PRINT) .
            'INVALID: ' . json_encode($message->invalidVariables, JSON_PRETTY_PRINT)
        );
    }
}
