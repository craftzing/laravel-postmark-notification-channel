<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

interface Config
{
    public function postmarkToken(): string;

    public function postmarkBaseUri(): ?string;

    public function usesPostmarkAsDefaultMailer(): bool;

    public function defaultSenderEmail(): string;

    public function defaultSenderName(): string;
}
