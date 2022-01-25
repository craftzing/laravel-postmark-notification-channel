<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

final class PostmarkErrorCodes
{
    public const RECIPIENT_IS_INACTIVE = 406;
    public const INVALID_TEMPLATE_MODEL = 403;
}
