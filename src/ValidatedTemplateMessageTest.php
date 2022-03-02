<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Doubles\FakeTemplatesApi;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\WithFaker;
use Generator;
use PHPUnit\Framework\TestCase;
use Postmark\Models\DynamicResponseModel;

use function optional;

final class ValidatedTemplateMessageTest extends TestCase
{
    use WithFaker;

    private const SUGGESTED_MODEL = [
        'project' => 'project_Value',
        'user' => [
            'email' => 'email_Value',
            'name' => 'name_Value',
            'preferences' => [
                [
                    'name' => 'name_Value',
                    'enabled' => 'enabled_Value',
                ],
            ],
        ],
        'list' => [
            ['item' => 'itemValue'],
        ],
    ];

    public function validTemplateModels(): Generator
    {
        $this->setupFaker();

        yield 'Fully filled out template model' => [
            DynamicTemplateModel::fromVariables([
                'project' => $this->faker->name,
                'user' => [
                    'email' => $this->faker->email,
                    'name' => $this->faker->firstName,
                    'preferences' => [
                        [
                            'name' => $this->faker->word,
                            'enabled' => $this->faker->randomElement(['true', 'false']),
                        ],
                    ],
                ],
                'list' => [
                    ['item' => $this->faker->word],
                    ['item' => $this->faker->word],
                ],
            ]),
        ];

        yield 'Template model with empty lists' => [
            DynamicTemplateModel::fromVariables([
                'project' => $this->faker->name,
                'user' => [
                    'email' => $this->faker->email,
                    'name' => $this->faker->firstName,
                    'preferences' => [],
                ],
                'list' => [],
            ]),
        ];
    }

    /**
     * @test
     * @dataProvider validTemplateModels
     */
    public function itCanBeConstructedValidTemplateModels(DynamicTemplateModel $templateModel): void
    {
        $renderedTemplate = new DynamicResponseModel(FakeTemplatesApi::RENDERED_TEMPLATE);
        $suggestedModel = new DynamicResponseModel(self::SUGGESTED_MODEL);

        $validatedMessage = ValidatedTemplateMessage::validate($renderedTemplate, $templateModel, $suggestedModel);

        $this->assertSame(FakeTemplatesApi::RENDERED_TEMPLATE['Subject']['RenderedContent'], $validatedMessage->subject);
        $this->assertSame(FakeTemplatesApi::RENDERED_TEMPLATE['HtmlBody']['RenderedContent'], $validatedMessage->htmlBody);
        $this->assertSame(FakeTemplatesApi::RENDERED_TEMPLATE['TextBody']['RenderedContent'], $validatedMessage->textBody);
        $this->assertEmpty($validatedMessage->missingVariables);
        $this->assertEmpty($validatedMessage->invalidVariables);
        $this->assertFalse($validatedMessage->isInvalid());
    }

    public function emptyBodyModels(): Generator
    {
        yield 'Response model with empty text' => [
            new DynamicResponseModel([
                'AllContentIsValid' => true,
                'Subject' => [
                    'RenderedContent' => $subject = 'Some rendered subject',
                ],
                'HtmlBody' => [
                    'RenderedContent' => $htmlBody = 'Some rendered HTML',
                ],
                'TextBody' => null,
            ]),
            compact('subject', 'htmlBody') + ['textBody' => '']
        ];

        yield 'Response model with empty html' => [
            new DynamicResponseModel([
                'AllContentIsValid' => true,
                'Subject' => [
                    'RenderedContent' => $subject ='Some rendered subject',
                ],
                'HtmlBody' => null,
                'TextBody' => [
                    'RenderedContent' => $textBody = 'Some rendered text',
                ],
            ]),
            compact('subject', 'textBody') + ['htmlBody' => '']
        ];

        yield 'Response model with empty html and text' => [
            new DynamicResponseModel([
                'AllContentIsValid' => true,
                'Subject' => [
                    'RenderedContent' => $subject = 'Some rendered subject',
                ],
                'HtmlBody' => null,
                'TextBody' => null,
            ]),
            compact('subject') + ['htmlBody' => '', 'textBody' => '']
        ];
    }

    /**
     * @test
     * @dataProvider emptyBodyModels
     */
    public function itCanHandleEmptyBodyValues(DynamicResponseModel $renderedTemplate, array $expectations): void
    {
        $this->setupFaker();
        $templateModel = DynamicTemplateModel::fromVariables([
            'project' => $this->faker->name,
            'user' => [
                'email' => $this->faker->email,
                'name' => $this->faker->firstName,
                'preferences' => [],
            ],
            'list' => [],
        ]);
        $suggestedModel = new DynamicResponseModel(self::SUGGESTED_MODEL);

        $validatedMessage = ValidatedTemplateMessage::validate($renderedTemplate, $templateModel, $suggestedModel);

        $this->assertSame($expectations['subject'], $validatedMessage->subject);
        $this->assertSame($expectations['htmlBody'], $validatedMessage->htmlBody);
        $this->assertSame($expectations['textBody'], $validatedMessage->textBody);
        $this->assertEmpty($validatedMessage->missingVariables);
        $this->assertEmpty($validatedMessage->invalidVariables);
        $this->assertFalse($validatedMessage->isInvalid());
    }

    public function invalidTemplateModels(): Generator
    {
        $this->setupFaker();

        yield 'Template model is empty' => [
            DynamicTemplateModel::fromVariables([]),
            self::SUGGESTED_MODEL,
            [],
        ];

        yield 'Template model is missing nested attributes' => [
            DynamicTemplateModel::fromVariables([
                'project' => self::SUGGESTED_MODEL['project'],
                'user' => [],
                'list' => [
                    ['nonExistingAttribute' => $this->faker->word],
                ],
            ]),
            [
                'user' => self::SUGGESTED_MODEL['user'],
                'list' => self::SUGGESTED_MODEL['list'],
            ],
            [],
        ];

        yield 'Template model is partially missing nested attributes' => [
            DynamicTemplateModel::fromVariables([
                'project' => self::SUGGESTED_MODEL['project'],
                'user' => [
                    'email' => $this->faker->email,
                    'name' => $this->faker->firstName,
                ],
                'list' => [],
            ]),
            [
                'user' => [
                    'preferences' => self::SUGGESTED_MODEL['user']['preferences'],
                ],
            ],
            [],
        ];

        yield 'Template model contains list items with missing attributes' => [
            DynamicTemplateModel::fromVariables([
                'project' => $this->faker->word,
                'user' => [
                    'email' => $this->faker->email,
                    'name' => $this->faker->firstName,
                    'preferences' => [
                        ['nonExistingAttribute' => $this->faker->word],
                        ['nonExistingAttribute' => $this->faker->word],
                    ],
                ],
                'list' => [
                    [],
                    [],
                    [],
                ],
            ]),
            [
                'user' => [
                    'preferences' => [
                        self::SUGGESTED_MODEL['user']['preferences'][0],
                        self::SUGGESTED_MODEL['user']['preferences'][0],
                    ],
                ],
                'list' => [
                    self::SUGGESTED_MODEL['list'][0],
                    self::SUGGESTED_MODEL['list'][0],
                    self::SUGGESTED_MODEL['list'][0],
                ],
            ],
            [],
        ];

        yield 'Template model contains attributes with an invalid type' => [
            DynamicTemplateModel::fromVariables([
                'project' => [
                    'name' => $this->faker->word,
                ],
                'user' => $this->faker->userName,
                'list' => [
                    'invalid' => 'value',
                ],
            ]),
            [],
            self::SUGGESTED_MODEL,
        ];

        yield 'Template model contains nested attributes with an invalid type' => [
            DynamicTemplateModel::fromVariables([
                'project' => $this->faker->word,
                'user' => [
                    'email' => $this->faker->email,
                    'name' => $this->faker->firstName,
                    'preferences' => [],
                ],
                'list' => [
                    [
                        'item' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ]),
            [],
            [
                'list' => self::SUGGESTED_MODEL['list'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidTemplateModels
     */
    public function itCanBeConstructedFromInvalidTemplateModels(
        DynamicTemplateModel $templateModel,
        array $expectedMissing,
        array $expectedInvalid
    ): void {
        $renderedTemplate = new DynamicResponseModel(FakeTemplatesApi::RENDERED_TEMPLATE);
        $suggestedModel = new DynamicResponseModel(self::SUGGESTED_MODEL);

        $validatedMessage = ValidatedTemplateMessage::validate($renderedTemplate, $templateModel, $suggestedModel);

        $this->assertEquals($expectedMissing, $validatedMessage->missingVariables);
        $this->assertEquals($expectedInvalid, $validatedMessage->invalidVariables);
        $this->assertTrue($validatedMessage->isInvalid());
    }

    public function isContentParseable(): Generator
    {
        yield 'Template content is parseable' => [true];
        yield 'Template content is not parseable' => [false];
    }

    /**
     * @test
     * @dataProvider isContentParseable
     */
    public function itCanCheckIfTheTemplateContentIsParseable(bool $isContentParseable): void
    {
        $validatedMessage = ValidatedTemplateMessage::validate(
            new DynamicResponseModel([
                'AllContentIsValid' => $isContentParseable,
            ] + FakeTemplatesApi::RENDERED_TEMPLATE),
            DynamicTemplateModel::fromVariables([]),
            new DynamicResponseModel(self::SUGGESTED_MODEL),
        );

        $this->assertEquals($isContentParseable, $validatedMessage->isContentParseable());
    }
}
