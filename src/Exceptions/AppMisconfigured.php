<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Exception;

final class AppMisconfigured extends Exception
{
    public static function missingPostmarkToken(): self
    {
        return new self(
            'Please make sure to provide a Postmark token by either setting the ' .
            '"services.postmark.token" config or the according environment variable.'
        );
    }

    public static function missingDefaultSenderEmail(): self
    {
        return new self(
            'Please make sure to provide a default sender email by either setting ' .
            'the "mail.from.address" config or the according environment variable.'
        );
    }

    public static function missingDefaultSenderName(): self
    {
        return new self(
            'Please make sure to provide a default sender name by either setting ' .
            'the "mail.from.name" config or the according environment variable.'
        );
    }
}
