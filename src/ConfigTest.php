<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\PostmarkTemplates;

use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Exceptions\AppMisconfigured;
use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Testing\IntegrationTestCase;
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
            ['laravel-postmark-templates-notification-channel.value' => null],
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
