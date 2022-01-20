<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\FakeExceptionHandler;
use Craftzing\Laravel\NotificationChannels\Postmark\FakeConfig;
use Craftzing\Laravel\NotificationChannels\Postmark\ServiceProvider;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

use function config;

abstract class IntegrationTestCase extends OrchestraTestCase
{
    use WithFaker;

    protected bool $shouldFakeEvents = true;
    protected bool $shouldFakeConfig = true;

    protected function setUpTraits(): array
    {
        Bus::fake();
        Queue::fake();
        Storage::fake();
        FakeExceptionHandler::swap($this->app);
        Postmark::fake();

        if ($this->shouldFakeEvents) {
            Event::fake();
        }

        if ($this->shouldFakeConfig) {
            FakeConfig::swap($this->app);
        }

        return parent::setUpTraits();
    }

    /**
     * @before
     */
    public function setupConfig(): void
    {
        $this->afterApplicationCreated(function (): void {
            config([
                'services.postmark.token' => $this->faker()->md5,
                'mail.from.address' => $this->faker()->email,
                'mail.from.name' => $this->faker()->name,
            ]);
        });
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
