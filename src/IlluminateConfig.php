<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\PostmarkTemplates;

use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Exceptions\AppMisconfigured;
use Illuminate\Contracts\Config\Repository;

final class IlluminateConfig implements Config
{
    private string $value;

    public function __construct(Repository $config)
    {
        $this->value = $this->resolveValue($config);
    }

    private function resolveValue(Repository $config): string
    {
        if (! ($value = $config->get('laravel-postmark-templates-notification-channel.value'))) {
            throw AppMisconfigured::missingConfigValue();
        }

        return $value;
    }
}
