<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Exceptions\CouldNotValidateNotification;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Resources\Recipients;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Facades\Postmark;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\IntegrationTestCase;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\WithFaker;

final class SdkTemplatesApiE2eTest extends IntegrationTestCase
{
    use WithFaker;

    private SdkTemplatesApi $templatesApi;

    /**
     * @before
     */
    public function setupTemplatesApi(): void
    {
        $this->afterApplicationCreated(function (): void {
            $this->enableSendingViaMailChannel();
            Postmark::dontFake();

            $this->templatesApi = $this->app[SdkTemplatesApi::class];
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
    public function itFailsWhenValidatingATemplateMessageThatDoesNotExist(): void
    {
        $message = TemplateMessage::fromAlias('nonsense')
            ->model(DynamicTemplateModel::fromVariables(['name' => 'foo']));

        $this->expectException(CouldNotValidateNotification::class);

        $this->templatesApi->validate($message);
    }

    /**
     * @test
     */
    public function itCanValidateTemplateMessages(): void
    {
        $message = TemplateMessage::fromAlias('ci-template')
            ->model(
                DynamicTemplateModel::fromVariables([
                    'project' => 'foo',
                    'templateName' => 'bar',
                    'ci' => [
                        'repo' => 'laravel-postmark-notification-channel',
                        'build' => '87483743',
                    ],
                    'templateHtmlItems' => [
                        [
                            'name' => $this->faker->word,
                            'url' => $this->faker->url,
                        ],
                    ],
                    'layoutHtmlList' => [
                        ['name' => $this->faker->word],
                    ],
                    'templateTextList' => [
                        [
                            'name' => $this->faker->word,
                            'url' => $this->faker->url,
                        ],
                    ],
                    'layoutTextList' => [
                        ['name' => $this->faker->word],
                    ],
                ]),
            )
            ->bcc(Recipients::fromEmails('fake@craftzing.com'));

        $validatedTemplateMessage = $this->templatesApi->validate($message);

        // Note that we should only check the validated template message on a high level.
        // The specific scenarios for which variable/attributes are missing or invalid
        // should be unit tested in the ValidatedTemplateMessageTest...
        $this->assertTrue($validatedTemplateMessage->isContentParseable());
        $this->assertFalse($validatedTemplateMessage->isInvalid());
    }
}
