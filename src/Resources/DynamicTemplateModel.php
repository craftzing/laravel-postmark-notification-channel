<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

final class DynamicTemplateModel implements TemplateModel
{
    /**
     * @var array<string|mixed>
     */
    private array $attributes;

    /**
     * @param array<string|mixed> $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function set(string $attribute, string $value): self
    {
        return new self([$attribute => $value] + $this->attributes);
    }

    /**
     * @return array<string|mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
