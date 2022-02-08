<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateId;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\WithFaker;
use Illuminate\Notifications\Notification;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
final class TemplateNotification extends Notification
{
    use WithFaker;

    private TemplateMessage $message;

    public function __construct(?TemplateMessage $message = null)
    {
        $this->setupFaker();
        $this->message = $message ?: new TemplateMessage(
            TemplateId::fromId($this->faker->randomNumber()),
        );
    }

    public function toPostmarkTemplate(): TemplateMessage
    {
        return $this->message;
    }
}
