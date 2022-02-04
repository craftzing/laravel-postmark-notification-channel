<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotValidateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\FakeTemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesApi as TemplatesApiInterface;
use Craftzing\Laravel\NotificationChannels\Postmark\ValidatedTemplateMessage;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void assertSent(TemplateMessage $message)
 * @see FakeTemplatesApi::assertSent
 *
 * @method static void assertNothingSent()
 * @see FakeTemplatesApi::assertNothingSent
 *
 * @method static CouldNotSendNotification failToSend()
 * @see FakeTemplatesApi::failToSend
 *
 * @method static void assertValidated(TemplateMessage $message)
 * @see FakeTemplatesApi::assertValidated
 *
 * @method static void assertNothingValidated()
 * @see FakeTemplatesApi::assertNothingValidated
 *
 * @method static CouldNotValidateNotification failToValidate()
 * @see FakeTemplatesApi::failToValidate
 *
 * @method static ValidatedTemplateMessage respondWithNonParseableTemplateContent()
 * @see FakeTemplatesApi::respondWithNonParseableTemplateContent
 *
 * @method static ValidatedTemplateMessage respondWithInvalidTemplateMessage()
 * @see FakeTemplatesApi::respondWithInvalidTemplateMessage
 */
final class TemplatesApi extends Facade
{
    public static function fake(): void
    {
        self::$app->instance(self::getFacadeAccessor(), new FakeTemplatesApi());
    }

    protected static function getFacadeAccessor(): string
    {
        return TemplatesApiInterface::class;
    }
}
