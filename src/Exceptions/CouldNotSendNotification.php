<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Exception;
use Postmark\Models\PostmarkException;

final class CouldNotSendNotification extends Exception
{
    public static function recipientIsInactive(PostmarkException $postmarkException): self
    {
        return new self(
            'One or more recipients are inactive: ' .
            "`$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
        );
    }

    public static function invalidTemplateModel(PostmarkException $postmarkException): self
    {
        return new self(
            'The template model is invalid: ' .
            "`$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
        );
    }

    public static function templateIdIsInvalidOrNotFound(PostmarkException $postmarkException): self
    {
        return new self(
            'The TemplateId is invalid or not found: ' .
            "`$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
        );
    }

    public static function templateContentIsInvalid(): self
    {
        return new self('The Template content is invalid.');
    }

    public static function requestToPostmarkApiFailed(PostmarkException $postmarkException): self
    {
        return new self(
            'The request to the Postmark failed while trying to send the notification: ' .
            "`$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
            0,
            $postmarkException,
        );
    }
}
