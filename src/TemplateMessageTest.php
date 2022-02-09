<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateId;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateIdentifier;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\WithFaker;
use Generator;
use PHPUnit\Framework\TestCase;
use Postmark\Models\PostmarkAttachment;

final class TemplateMessageTest extends TestCase
{
    use WithFaker;

    public function namedConstructors(): Generator
    {
        yield 'fromAlias' => [
            TemplateMessage::fromAlias($alias = 'some-alias'),
            TemplateAlias::fromAlias($alias),
        ];

        yield 'fromId' => [
            TemplateMessage::fromId($id = 39479357),
            TemplateId::fromId($id),
        ];
    }

    /**
     * @test
     * @dataProvider namedConstructors
     */
    public function itCanBeInitialisedFromANamedConstructor(
        TemplateMessage $message,
        TemplateIdentifier $expectedIdentifier
    ): void {
        $this->assertEquals($expectedIdentifier, $message->identifier);
        $this->assertEquals(DynamicTemplateModel::fromVariables([]), $message->model);
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
    public function itAcceptsAnOptionalModel(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $model = $this->createMock(TemplateModel::class);

        $message = $originalMessage->model($model);

        $this->assertEquals(DynamicTemplateModel::fromVariables([]), $originalMessage->model);
        $this->assertEquals($model, $message->model);
    }

    /**
     * @test
     */
    public function itAcceptsAnOptionalSender(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $sender = Sender::fromEmail($this->faker->email);

        $message = $originalMessage->from($sender);

        $this->assertNull($originalMessage->sender);
        $this->assertSame($sender, $message->sender);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalRecipients(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $recipients = Recipients::fromEmails($this->faker->email);

        $message = $originalMessage->to($recipients);

        $this->assertNull($originalMessage->recipients);
        $this->assertSame($recipients, $message->recipients);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalBcc(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $recipients = Recipients::fromEmails($this->faker->email);

        $message = $originalMessage->bcc($recipients);

        $this->assertNull($originalMessage->bcc);
        $this->assertSame($recipients, $message->bcc);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalHeaders(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $headers = [$this->faker->word => $this->faker->word];

        $message = $originalMessage->headers($headers);

        $this->assertNull($originalMessage->headers);
        $this->assertSame($headers, $message->headers);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalAttachments(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $attachments = [
            $this->createMock(PostmarkAttachment::class),
        ];

        $message = $originalMessage->attachments(...$attachments);

        $this->assertNull($originalMessage->attachments);
        $this->assertSame($attachments, $message->attachments);
    }

    /**
     * @test
     */
    public function itCanOptionallyActivateOpensTracking(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);

        $message = $originalMessage->trackOpens();

        $this->assertNull($originalMessage->trackOpens);
        $this->assertTrue($message->trackOpens);
    }

    /**
     * @test
     */
    public function itCanOptionallyDeactivateOpensTracking(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);

        $message = $originalMessage->dontTrackOpens();

        $this->assertNull($originalMessage->trackOpens);
        $this->assertFalse($message->trackOpens);
    }

    /**
     * @test
     */
    public function itAcceptsAOptionalTrackLinksConfiguration(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $trackLinks = new TrackLinks($this->faker->randomElement(TrackLinks::toArray()));

        $message = $originalMessage->trackLinks($trackLinks);

        $this->assertNull($originalMessage->trackLinks);
        $this->assertSame($trackLinks, $message->trackLinks);
    }

    /**
     * @test
     */
    public function itAcceptsAShortcutToTrackEverything(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);

        $message = $originalMessage->trackEverything();

        $this->assertNull($originalMessage->trackLinks);
        $this->assertTrue($message->trackLinks->equals(TrackLinks::HTML_AND_TEXT()));
    }

    /**
     * @test
     */
    public function itAcceptsAnOptionalTag(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $tag = $this->faker->word;

        $message = $originalMessage->tag($tag);

        $this->assertNull($originalMessage->tag);
        $this->assertSame($tag, $message->tag);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalMetadata(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $metadata = [$this->faker->word => $this->faker->word];

        $message = $originalMessage->metadata($metadata);

        $this->assertNull($originalMessage->metadata);
        $this->assertSame($metadata, $message->metadata);
    }

    /**
     * @test
     */
    public function itAcceptsOptionalMessageStream(): void
    {
        $originalMessage = TemplateMessage::fromAlias($this->faker->word);
        $messageStream = [$this->faker->word, $this->faker->word];

        $message = $originalMessage->messageStream(...$messageStream);

        $this->assertNull($originalMessage->messageStream);
        $this->assertSame($messageStream, $message->messageStream);
    }
}
