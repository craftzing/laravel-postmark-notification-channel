<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

interface TemplateIdentifier
{
    public function __toString(): string;

    public function toString(): string;

    /**
     * @return int|string
     */
    public function get();
}
