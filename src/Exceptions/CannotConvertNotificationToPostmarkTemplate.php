<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Illuminate\Notifications\Notification;
use LogicException;

use function get_class;

final class CannotConvertNotificationToPostmarkTemplate extends LogicException
{
    public static function missingToPostmarkTemplateMethod(Notification $notification): self
    {
        $class = get_class($notification);

        return new self(
            "$class is missing a `toPostmarkTemplate()` method in " .
            'order to be converted to a Postmark Template message.'
        );
    }
}
