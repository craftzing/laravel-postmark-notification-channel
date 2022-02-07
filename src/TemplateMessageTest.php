<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateIdentifier;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use Postmark\Models\PostmarkAttachment;

final class TemplateMessageTest extends TestCase
{
    use WithFaker;

    private TemplateMessage $message;

    /**
     * @before
     */
    public function setupMessage(): void
    {
        $this->message = new TemplateMessage(
            $this->createMock(TemplateIdentifier::class),
            $this->createMock(TemplateModel::class),
        );
    }

    /**
     * @after
     */
    public function unsetMessage(): void
    {
        unset($this->message);
    }

    /**
     * @test
     */
    public function itCanBeInitialisedWithAnyTemplateIdentifierAndModelImplementations(): void
    {
        $identifier = $this->createMock(TemplateIdentifier::class);
        $model = $this->createMock(TemplateModel::class);

        $message = new TemplateMessage($identifier, $model);

        $this->assertInstanceOf(TemplateMessage::class, $message);
        $this->assertSame($identifier, $message->identifier);
        $this->assertSame($model, $message->model);
        $this->assertNull($message->sender);
        $this->assertNull($message->recipients);
        $this->assertNull($message->bcc);
        $this->assertNull($message->headers);
        $this->assertNull($message->attachments);
        $this->assertNull($message->trackOpens);
        $this->assertNull($message->trackLinks);
        $this->assertNull($message->tag);
        $this->assertNull($message->metadata);
        $this->assertNull($message->messageStream);
    }

    /**
     * @test
     */
    public function itAcceptsAnOptionalSender(): void
    {
        $sender = Sender::fromEmail($this->faker->email);

        $message = $this->message->from($sender);

        $this->assertNull($this->message->sender);
        $this->assertSame($sender, $message->sender);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalRecipients(): void
    {
        $recipients = Recipients::fromEmails($this->faker->email);

        $message = $this->message->to($recipients);

        $this->assertNull($this->message->recipients);
        $this->assertSame($recipients, $message->recipients);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalBcc(): void
    {
        $recipients = Recipients::fromEmails($this->faker->email);

        $message = $this->message->bcc($recipients);

        $this->assertNull($this->message->bcc);
        $this->assertSame($recipients, $message->bcc);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalHeaders(): void
    {
        $headers = [$this->faker->word => $this->faker->word];

        $message = $this->message->headers($headers);

        $this->assertNull($this->message->headers);
        $this->assertSame($headers, $message->headers);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalAttachments(): void
    {
        $attachments = [
            $this->createMock(PostmarkAttachment::class),
        ];

        $message = $this->message->attachments(...$attachments);

        $this->assertNull($this->message->attachments);
        $this->assertSame($attachments, $message->attachments);
    }

    /**
     * @test
     */
    public function itCanOptionallyActivateOpensTracking(): void
    {
        $message = $this->message->trackOpens();

        $this->assertNull($this->message->trackOpens);
        $this->assertTrue($message->trackOpens);
    }

    /**
     * @test
     */
    public function itCanOptionallyDeactivateOpensTracking(): void
    {
        $message = $this->message->dontTrackOpens();

        $this->assertNull($this->message->trackOpens);
        $this->assertFalse($message->trackOpens);
    }

    /**
     * @test
     */
    public function itAcceptsAOptionalTrackLinksConfiguration(): void
    {
        $trackLinks = new TrackLinks($this->faker->randomElement(TrackLinks::toArray()));

        $message = $this->message->trackLinks($trackLinks);

        $this->assertNull($this->message->trackLinks);
        $this->assertSame($trackLinks, $message->trackLinks);
    }

    /**
     * @test
     */
    public function itAcceptsAShortcutToTrackEverything(): void
    {
        $message = $this->message->trackEverything();

        $this->assertNull($this->message->trackLinks);
        $this->assertTrue($message->trackLinks->equals(TrackLinks::HTML_AND_TEXT()));
    }

    /**
     * @test
     */
    public function itAcceptsAnOptionalTag(): void
    {
        $tag = $this->faker->word;

        $message = $this->message->tag($tag);

        $this->assertNull($this->message->tag);
        $this->assertSame($tag, $message->tag);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalMetadata(): void
    {
        $metadata = [$this->faker->word => $this->faker->word];

        $message = $this->message->metadata($metadata);

        $this->assertNull($this->message->metadata);
        $this->assertSame($metadata, $message->metadata);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalMessageStream(): void
    {
        $messageStream = [$this->faker->word, $this->faker->word];

        $message = $this->message->messageStream(...$messageStream);

        $this->assertNull($this->message->messageStream);
        $this->assertSame($messageStream, $message->messageStream);
    }
}
