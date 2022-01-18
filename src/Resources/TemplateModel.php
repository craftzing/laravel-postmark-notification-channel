<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

interface TemplateModel
{
    public function attributes(): array;
}
