<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Config as ConfigFacade;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Illuminate\Notifications\ChannelManager;

use function config;

final class ServiceProviderTest extends IntegrationTestCase
{
    /**
     * @before
     */
    public function dontFakeConfig(): void
    {
        $this->afterApplicationCreated(function (): void {
            ConfigFacade::dontFake();
        });
    }

    /**
     * @test
     */
    public function itExposesTheDefaultPackageConfig(): void
    {
        $config = config('postmark-notification-channel');

        $this->assertFalse($config['send_via_mail_channel']);
    }

    /**
     * @test
     */
    public function itBindsADefaultImplementationForTheConfigInterface(): void
    {
        $config = $this->app[Config::class];

        $this->assertInstanceOf(IlluminateConfig::class, $config);
    }

    /**
     * @test
     */
    public function itBindsADefaultImplementationForTheTemplatesApiInterface(): void
    {
        $templatesApi = $this->app[TemplatesApi::class];

        $this->assertInstanceOf(SdkTemplatesApi::class, $templatesApi);
    }

    /**
     * @test
     */
    public function itBindsTheTemplateChannel(): void
    {
        $channel = $this->app[TemplatesChannel::class];

        $this->assertInstanceOf(TemplatesChannel::class, $channel);
    }

    /**
     * @test
     */
    public function itExtendsTheNotificationChannelsWithTheTemplatesChannel(): void
    {
        $channel = $this->app[ChannelManager::class]->channel(TemplatesChannel::class);

        $this->assertInstanceOf(TemplatesChannel::class, $channel);
    }
}
