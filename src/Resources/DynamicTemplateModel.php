<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

final class DynamicTemplateModel implements TemplateModel
{
    /**
     * @var array<string|mixed>
     */
    private array $variables;

    /**
     * @param array<string|mixed> $attributes
     */
    private function __construct(array $attributes)
    {
        $this->variables = $attributes;
    }

    /**
     * @param array<string|mixed> $variables
     */
    public static function fromVariables(array $variables): self
    {
        return new self($variables);
    }

    public function set(string $variable, string $value): self
    {
        return new self([$variable => $value] + $this->variables);
    }

    /**
     * @return array<string|mixed>
     */
    public function variables(): array
    {
        return $this->variables;
    }
}
