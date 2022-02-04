<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Enums\TrackLinks;
use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotSendNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Sender;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateAlias;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Postmark\Models\PostmarkAttachment;

final class SdkTemplatesApiTest extends IntegrationTestCase
{
    use WithFaker;

    private SdkTemplatesApi $templatesApi;
    private TemplateMessage $message;

    /**
     * @before
     */
    public function setupTemplatesApi(): void
    {
        $this->afterApplicationCreated(function (): void {
            $this->templatesApi = $this->app[SdkTemplatesApi::class];
            $this->message = (new TemplateMessage(
                TemplateAlias::fromAlias('welcome'),
                DynamicTemplateModel::fromAttributes(['foo' => 'bar']),
            ))
                ->from(Sender::fromEmail($this->faker()->email))
                ->to(Recipients::fromEmails($this->faker()->email))
                ->bcc(Recipients::fromEmails($this->faker()->email))
                ->headers(['header' => 'value'])
                ->attachments($this->createMock(PostmarkAttachment::class))
                ->trackOpens()
                ->trackLinks(TrackLinks::HTML_AND_TEXT())
                ->tag('test')
                ->metadata(['meta' => 'value'])
                ->messageStream('outgoing');
        });
    }

    /**
     * @after
     */
    public function unsetTemplatesApi(): void
    {
        unset($this->templatesApi, $this->message);
    }

    /**
     * @test
     */
    public function itFailsWhenThePostmarkApiRespondedWithAnErrorWhileSending(): void
    {
        $e = Postmark::failRequest();

        $this->expectExceptionObject(CouldNotSendNotification::requestToPostmarkApiFailed($e));

        $this->templatesApi->send($this->message);
    }

    /**
     * @test
     */
    public function itCanSendATemplateMessage(): void
    {
        $this->templatesApi->send($this->message);

        Postmark::assertSentEmailWithTemplate($this->message);
    }
}
