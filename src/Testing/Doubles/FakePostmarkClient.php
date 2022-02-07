<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\FakesExceptions;
use PHPUnit\Framework\Assert;
use Postmark\Models\DynamicResponseModel;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;

use function compact;

/**
 * @internal This implementation should only be used in tests, as it is export-ignored in the gitattributes.
 */
final class FakePostmarkClient extends PostmarkClient
{
    use FakesExceptions;

    /**
     * @var mixed[]
     */
    private array $emailSentWithTemplate = [];

    public function __construct(string $serverToken = 'some-fake-token')
    {
        parent::__construct($serverToken);
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmailWithTemplate(
        $from,
        $to,
        $templateIdOrAlias,
        $templateModel,
        $inlineCss = true,
        $tag = null,
        $trackOpens = null,
        $replyTo = null,
        $cc = null,
        $bcc = null,
        $headers = null,
        $attachments = null,
        $trackLinks = null,
        $metadata = null,
        $messageStream = null
    ) {
        $this->emailSentWithTemplate = compact(
            'from',
            'to',
            'templateIdOrAlias',
            'templateModel',
            'inlineCss',
            'tag',
            'trackOpens',
            'replyTo',
            'cc',
            'bcc',
            'headers',
            'attachments',
            'trackLinks',
            'metadata',
            'messageStream',
        );

        $this->throwExceptionWhenDefined();

        return new DynamicResponseModel([]);
    }

    public function assertSentEmailWithTemplate(TemplateMessage $message): void
    {
        Assert::assertNotNull($this->emailSentWithTemplate, 'Email was not sent with Postmark template.');
        Assert::assertSame($this->emailSentWithTemplate['from'], $message->sender->toString());
        Assert::assertSame($this->emailSentWithTemplate['to'], $message->recipients->toString());
        Assert::assertSame($this->emailSentWithTemplate['templateIdOrAlias'], $message->identifier->get());
        Assert::assertSame($this->emailSentWithTemplate['templateModel'], $message->model->attributes());
        Assert::assertSame($this->emailSentWithTemplate['inlineCss'], $message->inlineCss);
        Assert::assertSame($this->emailSentWithTemplate['tag'], $message->tag);
        Assert::assertSame($this->emailSentWithTemplate['trackOpens'], $message->trackOpens);
        Assert::assertSame($this->emailSentWithTemplate['bcc'], ((string) $message->bcc) ?: null);
        Assert::assertSame($this->emailSentWithTemplate['headers'], $message->headers);
        Assert::assertSame($this->emailSentWithTemplate['attachments'], $message->attachments);
        Assert::assertSame($this->emailSentWithTemplate['trackLinks'], ((string) $message->trackLinks) ?: null);
        Assert::assertSame($this->emailSentWithTemplate['metadata'], $message->metadata);
        Assert::assertSame($this->emailSentWithTemplate['messageStream'], $message->messageStream);
    }

    public function failRequest(): PostmarkException
    {
        return $this->exception = new PostmarkException();
    }
}
