<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

interface TemplateModel
{
    /**
     * @return array<string|mixed>
     */
    public function variables(): array;
}
