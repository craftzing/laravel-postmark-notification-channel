<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use PHPUnit\Framework\TestCase;

final class RenderedEmailTemplateMailTest extends TestCase
{
    /** @test */
    public function itCanBeConstructedFromRenderedContent(): void
    {
        $subject = 'Some Content Here';
        $htmlBody = 'Some HTML Content Here';
        $textBody = 'Some Text Content Here';

        $mail = RenderedEmailTemplateMail::fromRenderedContent($subject, $htmlBody, $textBody);

        $this->assertSame($subject, $mail->subject);
        $this->assertSame($htmlBody, $mail->htmlBody());
        $this->assertSame($textBody, $mail->textView);
    }

    /** @test */
    public function itCanBuildRenderdEmailTemplate(): void
    {
        $subject = 'Some Content Here';
        $htmlBody = 'Some HTML Content Here';
        $textBody = 'Some Text Content Here';
        $mail = RenderedEmailTemplateMail::fromRenderedContent($subject, $htmlBody, $textBody);

        // Laravel needs the build method on the mailable but doesn't use it when faking Mail.
        // For that reason we test it here since it isn't picked up in any other test case.
        $build = $mail->build();

        $this->assertSame($subject, $build->subject);
        $this->assertSame($htmlBody, $build->htmlBody());
        $this->assertSame($textBody, $build->textView);
    }
}
