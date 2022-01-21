<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Config as ConfigFacade;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Generator;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\MailChannel;

use function config;

final class ServiceProviderTest extends IntegrationTestCase
{
    /**
     * @before
     */
    public function dontFakeConfig(): void
    {
        $this->afterApplicationCreated(function () {
            ConfigFacade::dontFake();
            Postmark::dontFake();
        });
    }

    /**
     * @test
     */
    public function itExposesTheDefaultPackageConfig(): void
    {
        $config = config('postmark-notification-channel');

        $this->assertSame(TemplatesChannel::class, $config['channel']);
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

    public function postmarkChannelDefinedInConfig(): Generator
    {
        yield 'Default configuration' => [
            [],
            TemplatesChannel::class,
        ];

        yield 'Overwritten configuration' => [
            ['postmark-notification-channel.channel' => 'mail'],
            MailChannel::class,
        ];
    }

    /**
     * @test
     * @dataProvider postmarkChannelDefinedInConfig
     */
    public function itExtendsTheNotificationChannelsWithTheDefaultPostmarkChannelDefinedInTheConfig(
        array $config,
        string $expectedImplementation
    ): void {
        config($config);

        $channel = $this->app[ChannelManager::class]->channel('postmark');

        // Ensure only the "postmark" channel extension was overwritten from the config...
        $this->assertInstanceOf(
            TemplatesChannel::class,
            $this->app[ChannelManager::class]->channel(TemplatesChannel::class),
        );
        $this->assertInstanceOf($expectedImplementation, $channel);
    }
}
