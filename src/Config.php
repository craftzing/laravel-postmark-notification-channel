<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;

interface Config
{
    public function postmarkToken(): string;

    public function postmarkBaseUri(): ?string;

    public function defaultSender(): Sender;

    public function shouldSendViaMailChannel(): bool;
}
