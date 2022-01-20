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

    private function __construct(string ...$emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    public static function fromEmails(string ...$emailAddresses): self
    {
        return new self(...$emailAddresses);
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
