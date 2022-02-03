<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Exceptions;

use Postmark\Models\PostmarkException;
use RuntimeException;

final class RequestToPostmarkTemplatesApiFailed extends RuntimeException
{
    private const DEFAULT_MESSAGE = 'Request to Postmark Templates API failed';

    public function __construct(PostmarkException $postmarkException, string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct(
            "$message: `$postmarkException->httpStatusCode - $postmarkException->message " .
            "(API error code: $postmarkException->postmarkApiErrorCode)`",
        );
    }

    public static function recipientIsInactive(PostmarkException $postmarkException): self
    {
        return new self($postmarkException, 'One or more recipients are inactive');
    }

    public static function templateIdIsInvalidOrNotFound(PostmarkException $postmarkException): self
    {
        return new self($postmarkException, 'The TemplateId is invalid or not found');
    }

    public static function invalidTemplateModel(PostmarkException $postmarkException): self
    {
        return new self($postmarkException, 'The template model is invalid');
    }
}
