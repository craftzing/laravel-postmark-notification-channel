<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Illuminate\Support\Facades\Notification;

final class NotificationTestExample extends IntegrationTestCase
{
    /**
     * @test
     */
    public function itCan(): void
    {
        $notification = new TemplateNotification();

        Notification::assertCanSendAsPostmarkEmailTemplate($notification);
        PostmarkNotificationChannel::assertCanSend($notification);
    }
}
