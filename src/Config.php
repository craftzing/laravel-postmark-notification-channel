<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

interface Config
{
    public function channel(): string;

    public function postmarkToken(): string;

    public function postmarkBaseUri(): ?string;

    public function defaultSenderEmail(): string;

    public function defaultSenderName(): string;
}
