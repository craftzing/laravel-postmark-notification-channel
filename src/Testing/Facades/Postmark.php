<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades;

use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\FakePostmarkClient;
use Illuminate\Support\Facades\Facade;
use Postmark\PostmarkClient;

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
    public static function fake(): void
    {
        self::$app->instance(self::getFacadeAccessor(), new FakePostmarkClient());
    }

    protected static function getFacadeAccessor(): string
    {
        return PostmarkClient::class;
    }
}
