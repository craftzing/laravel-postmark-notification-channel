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
use Postmark\Models\PostmarkAttachment;

final class TemplateMessage
{
    /**
     * @readonly
     */
    public TemplateIdentifier $identifier;

    /**
     * @readonly
     */
    public TemplateModel $model;

    /**
     * @readonly
     */
    public bool $inlineCss = true;

    /**
     * @readonly
     */
    public ?Sender $sender = null;

    /**
     * @readonly
     */
    public ?Recipients $recipients = null;

    /**
     * @readonly
     */
    public ?Recipients $bcc = null;

    /**
     * @var string[]
     * @readonly
     */
    public ?array $headers = null;

    /**
     * @var \Postmark\Models\PostmarkAttachment[]
     * @readonly
     */
    public ?array $attachments = null;

    /**
     * @readonly
     */
    public ?bool $trackOpens = null;

    /**
     * @readonly
     */
    public ?TrackLinks $trackLinks = null;

    /**
     * @readonly
     */
    public ?string $tag = null;

    /**
     * @var array<string|mixed>
     * @readonly
     */
    public ?array $metadata = null;

    /**
     * @var string[]
     * @readonly
     */
    public ?array $messageStream = null;

    private function __construct(TemplateIdentifier $identifier, ?TemplateModel $model = null)
    {
        $this->identifier = $identifier;
        $this->model = $model ?: DynamicTemplateModel::fromVariables([]);
    }

    public static function fromAlias(string $alias): self
    {
        return new self(TemplateAlias::fromAlias($alias));
    }

    public static function fromId(int $id): self
    {
        return new self(TemplateId::fromId($id));
    }

    private function copy(): self
    {
        $instance = new self($this->identifier, $this->model);
        $instance->inlineCss = $this->inlineCss;
        $instance->sender = $this->sender;
        $instance->recipients = $this->recipients;
        $instance->bcc = $this->bcc;
        $instance->headers = $this->headers;
        $instance->attachments = $this->attachments;
        $instance->trackOpens = $this->trackOpens;
        $instance->trackLinks = $this->trackLinks;
        $instance->tag = $this->tag;
        $instance->metadata = $this->metadata;
        $instance->messageStream = $this->messageStream;

        return $instance;
    }

    public function model(TemplateModel $model): self
    {
        $instance = $this->copy();
        $instance->model = $model;

        return $instance;
    }

    public function from(Sender $sender): self
    {
        $instance = $this->copy();
        $instance->sender = $sender;

        return $instance;
    }

    public function to(Recipients $recipients): self
    {
        $instance = $this->copy();
        $instance->recipients = $recipients;

        return $instance;
    }

    public function bcc(Recipients $recipients): self
    {
        $instance = $this->copy();
        $instance->bcc = $recipients;

        return $instance;
    }

    /**
     * @param string[] $headers
     */
    public function headers(array $headers): self
    {
        $instance = $this->copy();
        $instance->headers = $headers;

        return $instance;
    }

    public function attachments(PostmarkAttachment ...$attachments): self
    {
        $instance = $this->copy();
        $instance->attachments = $attachments;

        return $instance;
    }

    public function trackOpens(): self
    {
        $instance = $this->copy();
        $instance->trackOpens = true;

        return $instance;
    }

    public function dontTrackOpens(): self
    {
        $instance = $this->copy();
        $instance->trackOpens = false;

        return $instance;
    }

    public function trackLinks(TrackLinks $trackLinks): self
    {
        $instance = $this->copy();
        $instance->trackLinks = $trackLinks;

        return $instance;
    }

    public function trackEverything(): self
    {
        return $this->copy()
            ->trackOpens()
            ->trackLinks(TrackLinks::HTML_AND_TEXT());
    }

    public function tag(string $tag): self
    {
        $instance = $this->copy();
        $instance->tag = $tag;

        return $instance;
    }

    /**
     * @param string[] $metadata
     */
    public function metadata(array $metadata): self
    {
        $instance = $this->copy();
        $instance->metadata = $metadata;

        return $instance;
    }

    public function messageStream(string ...$messageStream): self
    {
        $instance = $this->copy();
        $instance->messageStream = $messageStream;

        return $instance;
    }
}
