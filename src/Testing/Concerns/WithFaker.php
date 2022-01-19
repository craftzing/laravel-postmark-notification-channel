<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns;

use Faker\Factory;
use Faker\Generator;

trait WithFaker
{
    protected ?Generator $faker = null;

    /**
     * @before
     */
    public function setupFaker(): void
    {
        $this->faker = $this->faker();
    }

    /**
     * @after
     */
    public function unset(): void
    {
        unset($this->faker);
    }

    protected function faker(): Generator
    {
        return Factory::create();
    }
}