<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\RequestToPostmarkTemplatesApiFailed;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\FakesExceptions;
use PHPUnit\Framework\Assert;
use Postmark\Models\PostmarkException;

/**
 * @internal This implementation should only be used for testing purposes.
 */
final class FakeTemplatesApi implements TemplatesApi
{
    use FakesExceptions;

    private ?TemplateMessage $sentMessage = null;

    public function send(TemplateMessage $message): void
    {
        $this->sentMessage = $message;

        $this->throwExceptionWhenDefined();
    }

    public function assertSent(TemplateMessage $message): void
    {
        Assert::assertEquals($this->sentMessage, $message);
    }

    public function validate(TemplateMessage $message): ValidatedTemplateMessage
    {
        $this->throwExceptionWhenDefined();
    }

    public function failRequestToPostmark(): RequestToPostmarkTemplatesApiFailed
    {
        return $this->exception = new RequestToPostmarkTemplatesApiFailed(new PostmarkException());
    }
}
