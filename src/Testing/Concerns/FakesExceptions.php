<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns;

use Exception;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
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
