<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Illuminate\Support\ServiceProvider as IluminateProvider;

final class ServiceProvider extends IluminateProvider
{
    public function register(): void
    {
        $this->app->bind(Config::class, IlluminateConfig::class);
    }
}
