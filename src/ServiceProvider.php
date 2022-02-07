<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider as IlluminateProvider;

final class ServiceProvider extends IlluminateProvider
{
    private const CONFIG_PATH = __DIR__ . '/../config/postmark-notification-channel.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'postmark-notification-channel');

        $this->app->bind(Config::class, Config::class);
        $this->app->bind(TemplatesApi::class, SdkTemplatesApi::class);
    }

    public function boot(ChannelManager $channels): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH => $this->app->configPath('postmark-notification-channel.php'),
            ], 'config');
        }

        $channels->extend(TemplatesChannel::class, fn (): TemplatesChannel => $this->app[TemplatesChannel::class]);
    }
}
