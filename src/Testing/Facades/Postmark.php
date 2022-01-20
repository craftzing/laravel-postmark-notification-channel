<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades;

use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\SpyPostmarkClient;
use Illuminate\Support\Facades\Facade;
use Postmark\PostmarkClient;

/**
 * @method static void assertSendEmailWithTemplate(TemplateMessage $message)
 * @see SpyPostmarkClient::assertSendEmailWithTemplate
 */
final class Postmark extends Facade
{
    public static function fake(): void
    {
        self::$app->instance(self::getFacadeAccessor(), new SpyPostmarkClient());
    }

    protected static function getFacadeAccessor(): string
    {
        return PostmarkClient::class;
    }
}
