<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Testing\Doubles;

use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

/**
 * @internal This implementation should only be used for testing purposes.
 */
final class FakeConfig implements Config
{
    public static function swap(Application $app): self
    {
        return $app->instance(Config::class, new self());
    }
}
