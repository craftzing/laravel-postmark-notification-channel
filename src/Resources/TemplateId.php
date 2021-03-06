<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

final class TemplateId implements TemplateIdentifier
{
    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function fromId(int $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function get(): int
    {
        return $this->value;
    }
}
