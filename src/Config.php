<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\AppMisconfigured;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Illuminate\Contracts\Config\Repository;

final class Config
{
    private Repository $config;
    private string $postmarkToken;
    private string $defaultSenderEmail;
    private string $defaultSenderName;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->postmarkToken = $this->configValue(
            'services.postmark.token',
            fn () => AppMisconfigured::missingPostmarkToken(),
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

    public function defaultSender(): Sender
    {
        return Sender::fromEmail($this->defaultSenderEmail)->as($this->defaultSenderName);
    }

    public function shouldSendViaMailChannel(): bool
    {
        return $this->config->get('postmark-notification-channel.send_via_mail_channel');
    }
}
