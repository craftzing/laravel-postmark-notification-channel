<?php

declare(strict_types=1);

namespace Craftzing\Laravel\:package_namespace;

use Craftzing\Laravel\:package_namespace\Exceptions\AppMisconfigured;
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
        if (! ($value = $config->get(':package_name.value'))) {
            throw AppMisconfigured::missingConfigValue();
        }

        return $value;
    }
}
