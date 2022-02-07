<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Illuminate\Notifications\RoutesNotifications;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
final class MailRoutingNotifiable
{
    use RoutesNotifications;

    public string $email = 'dev@craftzing.com';
}
