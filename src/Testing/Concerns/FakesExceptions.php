<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns;

use Exception;

trait FakesExceptions
{
    private ?Exception $exception = null;

    private function throwExceptionWhenDefined(): void
    {
        if ($this->exception) {
            throw $this->exception;
        }
    }
}
