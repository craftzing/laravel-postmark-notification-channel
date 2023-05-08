<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Extensions\Laravel\Testing;

use Craftzing\Laravel\NotificationChannels\Postmark\Extensions\PhpUnit\Constraints\IsSendableAsPostmarkTemplate;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use PHPUnit\Framework\Constraint\Constraint;

use function compact;

final class InteractsWithPostmarkTemplatesApiTest extends IntegrationTestCase
{
    use InteractsWithPostmarkTemplatesApi;

    private static array $assertNotificationIsSendableAsPostmarkTemplateParameters = [];

    /**
     * {@inheritdoc}
     */
    public static function assertThatPostmark($value, Constraint $constraint, string $message = ''): void
    {
        // Note that should overwrite this method in order to spy on the parameters it receives when calling
        // the `assertNotificationIsSendableAsPostmarkTemplate()` method of the trait we're testing...
        if ($constraint instanceof IsSendableAsPostmarkTemplate) {
            self::$assertNotificationIsSendableAsPostmarkTemplateParameters = compact('value', 'constraint', 'message');

            return;
        }

        parent::assertThat($value, $constraint, $message);
    }

    /**
     * @test
     */
    public function itCanBeUsedToAssertThatANotificationIsSendableAsPostmarkTemplate(): void
    {
        $notification = new TemplateNotification();

        $this->assertNotificationIsSendableAsPostmarkTemplate($notification);

        $this->assertEquals([
            'value' => $notification,
            'constraint' => $this->app[IsSendableAsPostmarkTemplate::class],
            'message' => '',
        ], self::$assertNotificationIsSendableAsPostmarkTemplateParameters);
    }
}
