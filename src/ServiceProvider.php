<?php

declare(strict_types=1);

namespace Craftzing\Laravel\:package_namespace;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../config/:package_name.php';

    public function boot(Router $router): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([self::CONFIG_PATH => $this->app->configPath(':package_name.php')], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, ':package_name');

        $this->app->bind(Config::class, IlluminateConfig::class);
    }
}
