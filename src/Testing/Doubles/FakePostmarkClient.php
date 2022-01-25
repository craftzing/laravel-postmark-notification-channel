<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\PostmarkErrorCodes;
use Craftzing\Laravel\NotificationChannels\Postmark\TemplateMessage;
use PHPUnit\Framework\Assert;
use Postmark\Models\DynamicResponseModel;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Symfony\Component\HttpFoundation\Response;

use function compact;
use function tap;

final class FakePostmarkClient extends PostmarkClient
{
    /**
     * @var mixed[]
     */
    private array $emailSentWithTemplate = [];

    private ?PostmarkException $exception = null;

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

        if ($this->exception) {
            throw $this->exception;
        }

        return new DynamicResponseModel([]);
    }

    public function assertSendEmailWithTemplate(TemplateMessage $message): void
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

    public function respondWithInactiveRecipientError(): PostmarkException
    {
        return $this->exception = tap(new PostmarkException(), function (PostmarkException $e): void {
            $e->httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $e->message = 'Recipient is inactive';
            $e->postmarkApiErrorCode = PostmarkErrorCodes::RECIPIENT_IS_INACTIVE;
        });
    }

    public function respondWithError(): PostmarkException
    {
        return $this->exception = new PostmarkException();
    }
}
