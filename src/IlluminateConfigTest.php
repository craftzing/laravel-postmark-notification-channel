<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\AppMisconfigured;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Exception;
use Generator;

use function config;

final class IlluminateConfigTest extends IntegrationTestCase
{
    use WithFaker;

    protected bool $shouldFakeConfig = false;

    private function requiredConfig(array $overwrites = []): array
    {
        return $overwrites + [
            'services.postmark.token' => $this->faker()->md5,
            'mail.default' => 'postmark',
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

        yield 'Default mailer is undefined' => [
            $this->requiredConfig(['mail.default' => null]),
            AppMisconfigured::missingDefaultMailer(),
        ];

        yield 'Default mailer is empty' => [
            $this->requiredConfig(['mail.default' => '']),
            AppMisconfigured::missingDefaultMailer(),
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
        $this->assertTrue($instance->usesPostmarkAsDefaultMailer());
        $this->assertSame(config('mail.from.address'), $instance->defaultSenderEmail());
        $this->assertSame(config('mail.from.name'), $instance->defaultSenderName());
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

    public function usesPostmarkAsDefaultMailer(): Generator
    {
        yield 'Default mailer is postmark' => [
            $this->requiredConfig(['mail.default' => 'postmark']),
            true,
        ];

        yield 'Default mailer is not postmark' => [
            $this->requiredConfig(['mail.default' => $this->faker()->randomElement([
                'mailgun',
                'smtp',
                'sendmail',
            ])]),
            false,
        ];
    }

    /**
     * @test
     * @dataProvider usesPostmarkAsDefaultMailer
     */
    public function itCanCheckIfPostmarkIsUsedAsDefaultMailer(array $config, bool $shouldBeUsingPostmark): void
    {
        config($config);

        $instance = $this->app[IlluminateConfig::class];

        $this->assertSame($shouldBeUsingPostmark, $instance->usesPostmarkAsDefaultMailer());
    }
}
