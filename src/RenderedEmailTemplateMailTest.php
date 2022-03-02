<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use PHPUnit\Framework\TestCase;

final class RenderedEmailTemplateMailTest extends TestCase
{
    /** @test */
    public function itCanCreateRenderdEmailTemplate(): void
    {
        $subject = 'Some Content Here';
        $htmlBody = 'Some HTML Content Here';
        $textBody = 'Some Text Content Here';

        $mail = RenderedEmailTemplateMail::fromRenderedContent($subject, $htmlBody, $textBody);

        $this->assertSame($subject, $mail->subject);
        $this->assertSame($textBody, $mail->textView);
    }

    /** @test */
    public function itCanBuildRenderdEmailTemplate(): void
    {
        $subject = 'Some Content Here';
        $htmlBody = 'Some HTML Content Here';
        $textBody = 'Some Text Content Here';
        $mail = RenderedEmailTemplateMail::fromRenderedContent($subject, $htmlBody, $textBody);

        $build = $mail->build();

        $this->assertSame($subject, $build->subject);
        $this->assertSame($textBody, $build->textView);
    }
}
