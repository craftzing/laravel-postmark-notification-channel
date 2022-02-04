<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Exception;
use Postmark\Models\PostmarkException;

final class CouldNotValidateNotification extends Exception
{
    public static function requestToPostmarkApiFailed(PostmarkException $e): self
    {
        return new self(
            'The request to the Postmark failed while trying to validate the notification: ' .
            "`$e->httpStatusCode - $e->message (API error code: $e->postmarkApiErrorCode)`",
        );
    }
}
