<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\DynamicTemplateModel;
use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Generator;
use PHPUnit\Framework\TestCase;
use Postmark\Models\DynamicResponseModel;

final class ValidatedTemplateModelTest extends TestCase
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

    public function successfulValidation(): Generator
    {
        $this->setupFaker();

        yield 'Fully filled out template model' => [
            DynamicTemplateModel::fromAttributes([
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
            DynamicTemplateModel::fromAttributes([
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
     * @dataProvider successfulValidation
     */
    public function itCanBeConstructedBySuccessfullyValidatingATemplateModelAgainstASuggestedModel(
        DynamicTemplateModel $templateModel
    ): void {
        $suggestedModel = new DynamicResponseModel(self::SUGGESTED_MODEL);

        $validatedModel = ValidatedTemplateModel::validate($templateModel, $suggestedModel);

        $this->assertEmpty($validatedModel->missing);
        $this->assertEmpty($validatedModel->invalid);
        $this->assertFalse($validatedModel->isIncompleteOrInvalid());
    }

    public function failedValidation(): Generator
    {
        $this->setupFaker();

        yield 'Template model is empty' => [
            DynamicTemplateModel::fromAttributes([]),
            self::SUGGESTED_MODEL,
            [],
        ];

        yield 'Template model is missing nested attributes' => [
            DynamicTemplateModel::fromAttributes([
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
            DynamicTemplateModel::fromAttributes([
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
            DynamicTemplateModel::fromAttributes([
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
                    'preferences' =>[
                        self::SUGGESTED_MODEL['user']['preferences'][0],
                        self::SUGGESTED_MODEL['user']['preferences'][0],
                    ],
                ],
                'list' =>[
                    self::SUGGESTED_MODEL['list'][0],
                    self::SUGGESTED_MODEL['list'][0],
                    self::SUGGESTED_MODEL['list'][0],
                ],
            ],
            [],
        ];

        yield 'Template model contains attributes with an invalid type' => [
            DynamicTemplateModel::fromAttributes([
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
    }

    /**
     * @test
     * @dataProvider failedValidation
     */
    public function itCanBeConstructedByFailingValidatingATemplateModelAgainstASuggestedModel(
        DynamicTemplateModel $templateModel,
        array $expectedMissing,
        array $expectedInvalid
    ): void {
        $suggestedModel = new DynamicResponseModel(self::SUGGESTED_MODEL);

        $validatedModel = ValidatedTemplateModel::validate($templateModel, $suggestedModel);

        $this->assertEquals($expectedMissing, $validatedModel->missing);
        $this->assertEquals($expectedInvalid, $validatedModel->invalid);
        $this->assertTrue($validatedModel->isIncompleteOrInvalid());
    }
}
