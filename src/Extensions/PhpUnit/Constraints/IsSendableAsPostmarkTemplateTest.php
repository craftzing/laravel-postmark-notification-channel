<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Extensions\PhpUnit\Constraints;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\FakeTemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\TemplateNotification;
use Generator;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class IsSendableAsPostmarkTemplateTest extends TestCase
{
    private IsSendableAsPostmarkTemplate $constraint;
    private FakeTemplatesApi $fakeTemplatesApi;

    /**
     * @before
     */
    public function setupConstraint(): void
    {
        $this->fakeTemplatesApi = new FakeTemplatesApi();
        $this->constraint = new IsSendableAsPostmarkTemplate(
            $this->fakeTemplatesApi,
        );
    }

    /**
     * @after
     */
    public function unset(): void
    {
        unset($this->constraint, $this->fakeTemplatesApi);
    }

    /**
     * @test
     */
    public function itCanBeUsedAsAPhpUnitConstraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->constraint);
    }

    public function invalidTestSubjects(): Generator
    {
        yield 'Subject is not an object' => ['not an object'];
        yield 'Subject is not a notification instance' => [new stdClass()];
        yield 'Subject is missing a Postmark TemplateMessage method' => [
            new class extends Notification {},
        ];
    }

    /**
     * @test
     * @dataProvider invalidTestSubjects
     */
    public function itFailsWhenTheSubjectIsInvalid($subject): void
    {
        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate($subject);
    }

    /**
     * @test
     */
    public function itFailsWhenTheTemplateContentOnPostmarkIsNotParseable(): void
    {
        $this->fakeTemplatesApi->respondWithNonParseableTemplateContent();

        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate(new TemplateNotification());
    }

    /**
     * @test
     */
    public function itFailsWhenTheProvidedNotificationMessageIsDeemedInvalidForThePostmarkTemplate(): void
    {
        $this->fakeTemplatesApi->respondWithInvalidTemplateMessage();

        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate(new TemplateNotification());
    }

    /**
     * @test
     */
    public function itPassesWhenTheProvidedNotificationMessageIsSendableAsPostmarkTemplate(): void
    {
        $isSendableAsPostmarkTemplate = $this->constraint->evaluate(new TemplateNotification(), '', true);

        $this->assertTrue($isSendableAsPostmarkTemplate);
    }
}
