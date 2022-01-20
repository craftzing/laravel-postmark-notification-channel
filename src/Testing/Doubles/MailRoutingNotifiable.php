<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Illuminate\Notifications\RoutesNotifications;

final class MailRoutingNotifiable
{
    use RoutesNotifications;

    public string $email = 'dev@craftzing.com';
}
