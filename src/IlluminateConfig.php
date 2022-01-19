<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\AppMisconfigured;
use Illuminate\Contracts\Config\Repository;

final class IlluminateConfig implements Config
{
    public const POSTMARK_DEFAULT_MAILER = 'postmark';

    private Repository $config;
    private string $postmarkToken;
    private string $defaultMailer;
    private string $defaultSenderEmail;
    private string $defaultSenderName;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->postmarkToken = $this->configValue(
            'services.postmark.token',
            fn () => AppMisconfigured::missingPostmarkToken(),
        );
        $this->defaultMailer = $this->configValue(
            'mail.default',
            fn () => AppMisconfigured::missingDefaultMailer(),
        );
        $this->defaultSenderEmail = $this->configValue(
            'mail.from.address',
            fn () => AppMisconfigured::missingDefaultSenderEmail(),
        );
        $this->defaultSenderName = $this->configValue(
            'mail.from.name',
            fn () => AppMisconfigured::missingDefaultSenderName(),
        );
    }

    private function configValue(string $configPath, callable $missingConfigException): string
    {
        if ($value = $this->config->get($configPath)) {
            return $value;
        }

        throw $missingConfigException();
    }

    public function postmarkToken(): string
    {
        return $this->postmarkToken;
    }

    public function postmarkBaseUri(): ?string
    {
        return $this->config->get('services.postmark.base_uri');
    }

    public function usesPostmarkAsDefaultMailer(): bool
    {
        return $this->defaultMailer === self::POSTMARK_DEFAULT_MAILER;
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
