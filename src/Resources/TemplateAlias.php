<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

final class TemplateAlias implements TemplateIdentifier
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromAlias(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function get(): string
    {
        return $this->value;
    }
}
