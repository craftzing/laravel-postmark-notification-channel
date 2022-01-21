<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Exception;
use Postmark\Models\PostmarkException;

final class CouldNotSendNotification extends Exception
{
    public const POSTMARK_API_ERROR_CODE_RECIPIENT_IS_INACTIVE = 406;

    public static function recipientIsInactive(PostmarkException $postmarkException): self
    {
        return new self(
            'One or more recipients are inactive: ' .
            "`$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
        );
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
