<?php

declare(strict_types=1);

namespace Craftzing\Laravel\:package_namespace;

use Craftzing\Laravel\:package_namespace\Exceptions\AppMisconfigured;
use Craftzing\Laravel\:package_namespace\Testing\IntegrationTestCase;
use Exception;
use Generator;
use Illuminate\Support\Str;

use function config;

final class ConfigTest extends IntegrationTestCase
{
    protected bool $shouldFakeConfig = false;

    public function misconfiguredApp(): Generator
    {
        yield 'Value is undefined' => [
            [':package_name.value' => null],
            AppMisconfigured::missingConfigValue(),
        ];
    }

    /**
     * @test
     * @dataProvider misconfiguredApp
     */
    public function itFailsToResolveWhenTheAppIsMisconfigured(array $config, Exception $exception): void
    {
        config($config);

        $this->expectExceptionObject($exception);

        $this->app[Config::class];
    }
}
