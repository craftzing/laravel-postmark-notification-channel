<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing;

use Faker\Factory;
use Faker\Generator;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
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
