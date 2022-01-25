<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

final class Sender
{
    private string $email;
    private string $name = '';

    private function __construct(string $email)
    {
        $this->email = $email;
    }

    public static function fromEmail(string $email): self
    {
        return new self($email);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->name) {
            return "$this->name <$this->email>";
        }

        return $this->email;
    }

    public function as(string $name): self
    {
        $instance = new self($this->email);
        $instance->name = $name;

        return $instance;
    }
}
