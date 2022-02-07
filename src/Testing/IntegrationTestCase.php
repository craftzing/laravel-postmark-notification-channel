<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing;

use Craftzing\Laravel\NotificationChannels\Postmark\ServiceProvider;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Config;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

use function config;
use function env;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
abstract class IntegrationTestCase extends OrchestraTestCase
{
    use WithFaker;

    protected function setUpTraits(): array
    {
        Bus::fake();
        Queue::fake();
        Storage::fake();
        Event::fake();
        Mail::fake();
        Config::fake();
        Postmark::fake();

        return parent::setUpTraits();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(__DIR__ . '/../../');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        config([
            'services.postmark.token' => env('POSTMARK_TOKEN'),
            'mail.from.address' => $this->faker()->email,
            'mail.from.name' => $this->faker()->name,
        ]);
    }

    /**
     * @return array<string>
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    /**
     * @param object $class
     * @return mixed
     */
    public function handle(object $class)
    {
        return $this->app->call([$class, 'handle']);
    }
}
