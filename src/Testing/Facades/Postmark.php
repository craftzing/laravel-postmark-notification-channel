<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades;

use Craftzing\Laravel\NotificationChannels\Postmark\Config as ConfigInterface;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\FakePostmarkClient;
use Illuminate\Support\Facades\Facade;
use LogicException;
use Postmark\PostmarkClient;

use function sprintf;

/**
 * @method static void assertSendEmailWithTemplate(TemplateMessage $message)
 * @see FakePostmarkClient::assertSendEmailWithTemplate
 *
 * @method static \Postmark\Models\PostmarkException respondWithInactiveRecipientError
 * @see FakePostmarkClient::respondWithInactiveRecipientError
 *
 * @method static \Postmark\Models\PostmarkException respondWithError
 * @see FakePostmarkClient::respondWithError
 */
final class Postmark extends Facade
{
    private static ?PostmarkClient $implementation = null;

    public static function fake(): void
    {
        self::$implementation = new PostmarkClient(self::$app[ConfigInterface::class]->postmarkToken());

        self::$app->instance(self::getFacadeAccessor(), new FakePostmarkClient());
    }

    public static function dontFake(): void
    {
        if (! self::$implementation) {
            throw new LogicException(sprintf("`%s` has not been faked.", self::getFacadeAccessor()));
        }

        self::$app->instance(self::getFacadeAccessor(), self::$implementation);
    }

    protected static function getFacadeAccessor(): string
    {
        return PostmarkClient::class;
    }
}
