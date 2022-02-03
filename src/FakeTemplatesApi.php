<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\RequestToPostmarkTemplatesApiFailed;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\TemplateContentIsNotParseable;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\FakesExceptions;
use Exception;
use PHPUnit\Framework\Assert;
use Postmark\Models\DynamicResponseModel;
use Postmark\Models\PostmarkException;

/**
 * @internal This implementation should only be used for testing purposes.
 */
final class FakeTemplatesApi implements TemplatesApi
{
    use FakesExceptions;

    public const RENDERED_TEMPLATE = [
        'Subject' => [
            'RenderedContent' => 'Some rendered subject',
        ],
        'HtmlBody' => [
            'RenderedContent' => 'Some rendered HTML',
        ],
        'TextBody' => [
            'RenderedContent' => 'Some rendered text',
        ],
    ];

    private ?TemplateMessage $sentMessage = null;
    private ?TemplateMessage $validatedMessage = null;
    private ?ValidatedTemplateMessage $validatedTemplateMessage = null;
    private ?Exception $validationException = null;

    public function send(TemplateMessage $message): void
    {
        $this->sentMessage = $message;

        $this->throwExceptionWhenDefined();
    }

    public function assertSent(TemplateMessage $message): void
    {
        Assert::assertEquals($this->sentMessage, $message);
    }

    public function assertNothingSent(): void
    {
        Assert::assertNull($this->sentMessage);
    }

    public function validate(TemplateMessage $message): ValidatedTemplateMessage
    {
        $this->validatedMessage = $message;

        $this->throwExceptionWhenDefined();

        if ($this->validationException) {
            throw $this->validationException;
        }

        return $this->validatedTemplateMessage ?: ValidatedTemplateMessage::validate(
            new DynamicResponseModel(self::RENDERED_TEMPLATE),
            DynamicTemplateModel::fromAttributes(['foo' => 'bar']),
            new DynamicResponseModel(['foo' => 'foo_Value']),
        );
    }

    public function failToValidateTemplate(): ValidatedTemplateMessage
    {
        return $this->validatedTemplateMessage = ValidatedTemplateMessage::validate(
            new DynamicResponseModel(self::RENDERED_TEMPLATE),
            DynamicTemplateModel::fromAttributes([]),
            new DynamicResponseModel(['foo' => 'foo_Value']),
        );
    }

    public function assertValidated(TemplateMessage $message): void
    {
        Assert::assertEquals($this->validatedMessage, $message);
    }

    public function assertNothingValidated(): void
    {
        Assert::assertNull($this->validatedMessage);
    }

    public function failRequestToPostmark(): RequestToPostmarkTemplatesApiFailed
    {
        return $this->exception = new RequestToPostmarkTemplatesApiFailed(new PostmarkException());
    }

    public function failToParseTemplateContent(): TemplateContentIsNotParseable
    {
        return $this->validationException = new TemplateContentIsNotParseable();
    }
}
