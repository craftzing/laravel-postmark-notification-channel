<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\AppMisconfigured;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Exception;
use Generator;

use function config;

final class IlluminateConfigTest extends IntegrationTestCase
{
    private function requiredConfig(array $overwrites = []): array
    {
        return $overwrites + [
            'services.postmark.token' => $this->faker()->md5,
            'mail.from.address' => $this->faker()->email,
            'mail.from.name' => $this->faker()->name,
        ];
    }

    public function misconfiguredApp(): Generator
    {
        yield 'Postmark token is undefined' => [
            $this->requiredConfig(['services.postmark.token' => null]),
            AppMisconfigured::missingPostmarkToken(),
        ];

        yield 'Postmark token is empty' => [
            $this->requiredConfig(['services.postmark.token' => '']),
            AppMisconfigured::missingPostmarkToken(),
        ];

        yield 'Default email sender email is undefined' => [
            $this->requiredConfig(['mail.from.address' => null]),
            AppMisconfigured::missingDefaultSenderEmail(),
        ];

        yield 'Default email sender email is empty' => [
            $this->requiredConfig(['mail.from.address' => '']),
            AppMisconfigured::missingDefaultSenderEmail(),
        ];

        yield 'Default email sender name is undefined' => [
            $this->requiredConfig(['mail.from.name' => null]),
            AppMisconfigured::missingDefaultSenderName(),
        ];

        yield 'Default email sender name is empty' => [
            $this->requiredConfig(['mail.from.name' => '']),
            AppMisconfigured::missingDefaultSenderName(),
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

        $this->app[IlluminateConfig::class];
    }

    /**
     * @test
     */
    public function itCanReturnTheConfigurationValues(): void
    {
        $instance = $this->app[IlluminateConfig::class];

        $this->assertSame(config('postmark-notification-channel.channel'), $instance->channel());
        $this->assertSame(config('services.postmark.token'), $instance->postmarkToken());
        $this->assertEquals(
            Sender::fromEmail(config('mail.from.address'))->as(config('mail.from.name')),
            $instance->defaultSender(),
        );
    }

    public function postmarkBaseUri(): Generator
    {
        yield 'Postmark base URI is undefined' => [null];
        yield 'Postmark base URI is empty' => [''];
        yield 'Postmark base URI is defined' => [$this->faker()->url];
    }

    /**
     * @test
     * @dataProvider postmarkBaseUri
     */
    public function itOptionallyReturnsAPostmarkBaseUri(?string $postmarkBaseUri): void
    {
        config($this->requiredConfig(['services.postmark.base_uri' => $postmarkBaseUri]));

        $instance = $this->app[IlluminateConfig::class];

        $this->assertSame($postmarkBaseUri, $instance->postmarkBaseUri());
    }
}
