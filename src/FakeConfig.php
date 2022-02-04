<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Faker\Generator;

/**
 * @internal This implementation should only be used for testing purposes.
 */
final class FakeConfig implements Config
{
    private string $postmarkToken;
    private string $defaultSenderEmail;
    private string $defaultSenderName;
    private bool $shouldSendViaMailChannel = false;

    public function __construct(Generator $faker)
    {
        $this->postmarkToken = 'some-fake-token';
        $this->defaultSenderEmail = 'dev@craftzing.com';
        $this->defaultSenderName = $faker->name;
    }

    public function postmarkToken(): string
    {
        return $this->postmarkToken;
    }

    public function defaultSender(): Sender
    {
        return Sender::fromEmail($this->defaultSenderEmail)->as($this->defaultSenderName);
    }

    public function shouldSendViaMailChannel(): bool
    {
        return $this->shouldSendViaMailChannel;
    }

    public function enableSendingViaMailChannel(): void
    {
        $this->shouldSendViaMailChannel = true;
    }
}
