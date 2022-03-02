<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Illuminate\Mail\Mailable;

final class RenderedEmailTemplateMail extends Mailable
{
    public static function fromRenderedContent(string $subject, string $html, string $text): self
    {
        return (new self())
            ->subject($subject)
            ->html($html)
            ->text($text);
    }

    public function build(): self
    {
        return $this;
    }

    public function htmlBody(): string
    {
        return $this->html;
    }
}
