<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Craftzing\Laravel\NotificationChannels\Postmark\ValidatedTemplateMessage;
use Exception;

use function json_encode;

final class CouldNotSendNotification extends Exception
{
    public static function requestToPostmarkApiFailed(RequestToPostmarkTemplatesApiFailed $e): self
    {
        return new self("The request to the Postmark failed while trying to send the notification. {$e->getMessage()}");
    }

    public static function templateContentIsNotParseable(TemplateContentIsNotParseable $e): self
    {
        return new self($e->getMessage());
    }

    public static function templateMessageIsInvalid(ValidatedTemplateMessage $model): self
    {
        return new self(
            'The Template model is invalid. Make sure to adhere to the suggested template model: \n\n' .
            'MISSING:' . json_encode($model->missingVariables) .
            'INVALID:' . json_encode($model->invalidVariables)
        );
    }
}
