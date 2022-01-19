<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Faker\Generator;
use Illuminate\Contracts\Foundation\Application;

/**
 * @internal This implementation should only be used for testing purposes.
 */
final class FakeConfig implements Config
{
    private string $postmarkToken;
    private string $defaultSenderEmail;
    private string $defaultSenderName;
    private ?string $postmarkBaseUri;

    public function __construct(Generator $faker)
    {
        $this->postmarkToken = $faker->md5;
        $this->defaultSenderEmail = 'dev@craftzing.com';
        $this->defaultSenderName = $faker->name;
        $this->postmarkBaseUri = null;
    }

    public static function swap(Application $app): self
    {
        return $app->instance(Config::class, new self($app[Generator::class]));
    }

    public function postmarkToken(): string
    {
        return $this->postmarkToken;
    }

    public function postmarkBaseUri(): ?string
    {
        return $this->postmarkBaseUri;
    }

    public function usesPostmarkAsDefaultMailer(): bool
    {
        return true;
    }

    public function defaultSenderEmail(): string
    {
        return $this->defaultSenderEmail;
    }

    public function defaultSenderName(): string
    {
        return $this->defaultSenderName;
    }
}
