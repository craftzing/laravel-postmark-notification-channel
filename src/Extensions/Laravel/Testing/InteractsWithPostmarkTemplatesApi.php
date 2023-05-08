<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Extensions\Laravel\Testing;

use Craftzing\Laravel\NotificationChannels\Postmark\Extensions\PhpUnit\Constraints\IsSendableAsPostmarkTemplate;
use Illuminate\Notifications\Notification;

trait InteractsWithPostmarkTemplatesApi
{
    private function assertNotificationIsSendableAsPostmarkTemplate(Notification $notification): void
    {
        $this->assertThatPostmark($notification, $this->app[IsSendableAsPostmarkTemplate::class]);
    }
}
