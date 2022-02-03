<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;


interface TemplatesApi
{
    public const RECIPIENT_IS_INACTIVE = 406;
    public const INVALID_TEMPLATE_MODEL = 403;
    public const TEMPLATE_ID_INVALID_OR_NOT_FOUND = 1101;

    public function send(TemplateMessage $message): void;

    public function validate(TemplateMessage $message): ValidatedTemplateMessage;
}
