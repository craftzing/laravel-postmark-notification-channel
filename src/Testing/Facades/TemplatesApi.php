<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\RequestToPostmarkTemplatesApiFailed;
use Craftzing\Laravel\NotificationChannels\Postmark\FakeTemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplatesApi as TemplatesApiInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RequestToPostmarkTemplatesApiFailed failRequestToPostmark()
 * @see FakeTemplatesApi::failRequestToPostmark
 *
 * @method static void assertSent(TemplateMessage $message)
 * @see FakeTemplatesApi::assertSent
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
