<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Exception;
use Postmark\Models\DynamicResponseModel;

use function json_encode;

final class TemplateContentIsNotParseable extends Exception
{
    public static function fromTemplateResource(DynamicResponseModel $resource): self
    {
        return new self(
            'Postmark cannot parse the provided `Subject`, `HtmlBody` or `TextBody`: \n' .
            'Subject: ' . json_encode($resource['Subject'], JSON_PRETTY_PRINT) .
            'HtmlBody: ' . json_encode($resource['HtmlBody'], JSON_PRETTY_PRINT) .
            'TextBody: ' . json_encode($resource['TextBody'], JSON_PRETTY_PRINT)
        );
    }
}
