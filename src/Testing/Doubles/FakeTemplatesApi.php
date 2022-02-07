<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotValidateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\FakesExceptions;
use Craftzing\Laravel\NotificationChannels\Postmark\ValidatedTemplateMessage;
use Exception;
use PHPUnit\Framework\Assert;
use Postmark\Models\DynamicResponseModel;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
final class FakeTemplatesApi implements TemplatesApi
{
    use FakesExceptions;

    public const RENDERED_TEMPLATE = [
        'AllContentIsValid' => true,
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

    public function failToSend(): CouldNotSendNotification
    {
        return $this->exception = new CouldNotSendNotification();
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

    public function assertValidated(TemplateMessage $message): void
    {
        Assert::assertEquals($this->validatedMessage, $message);
    }

    public function assertNothingValidated(): void
    {
        Assert::assertNull($this->validatedMessage);
    }

    public function failToValidate(): CouldNotValidateNotification
    {
        return $this->exception = new CouldNotValidateNotification();
    }

    public function respondWithNonParseableTemplateContent(): ValidatedTemplateMessage
    {
        return $this->validatedTemplateMessage = ValidatedTemplateMessage::validate(
            new DynamicResponseModel(['AllContentIsValid' => false] + FakeTemplatesApi::RENDERED_TEMPLATE),
            DynamicTemplateModel::fromAttributes([]),
            new DynamicResponseModel(['foo' => 'foo_Value']),
        );
    }

    public function respondWithInvalidTemplateMessage(): ValidatedTemplateMessage
    {
        return $this->validatedTemplateMessage = ValidatedTemplateMessage::validate(
            new DynamicResponseModel(FakeTemplatesApi::RENDERED_TEMPLATE),
            DynamicTemplateModel::fromAttributes([]),
            new DynamicResponseModel(['foo' => 'foo_Value']),
        );
    }
}
