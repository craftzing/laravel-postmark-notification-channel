<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Testing;

use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Exceptions\FakeExceptionHandler;
use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\ServiceProvider;
use Craftzing\Laravel\NotificationChannels\PostmarkTemplates\Testing\Doubles\FakeConfig;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class IntegrationTestCase extends OrchestraTestCase
{
    protected bool $shouldFakeEvents = true;
    protected bool $shouldFakeConfig = true;

    protected function setUpTraits(): array
    {
        Bus::fake();
        Queue::fake();
        Storage::fake();
        FakeExceptionHandler::swap($this->app);

        if ($this->shouldFakeEvents) {
            Event::fake();
        }

        if ($this->shouldFakeConfig) {
            FakeConfig::swap($this->app);
        }

        return parent::setUpTraits();
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
