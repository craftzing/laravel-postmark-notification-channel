<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use function implode;

final class Recipients
{
    /**
     * @var string[]
     */
    private array $emailAddresses;

    public function __construct(string ...$emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return implode(',', $this->emailAddresses);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        return $this->emailAddresses;
    }
}
