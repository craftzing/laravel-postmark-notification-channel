<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static self NONE()
 * @method static self HTML_AND_TEXT()
 * @method static self HTML_ONLY()
 * @method static self TEXT_ONLY()
 */
final class TrackLinks extends Enum
{
    public const NONE = 'None';
    public const HTML_AND_TEXT = 'HtmlAndText';
    public const HTML_ONLY = 'HtmlOnly';
    public const TEXT_ONLY = 'TextOnly';
}
