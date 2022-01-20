<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use Generator;
use PHPUnit\Framework\TestCase;

final class RecipientsTest extends TestCase
{
    use WithFaker;

    public function emailAddresses(): Generator
    {
        yield 'Single email address' => [
            [$email = $this->faker()->email],
            $email,
        ];

        yield 'Multiple email addresses' => [
            [
                $firstEmail = $this->faker()->email,
                $lastEmail = $this->faker()->email,
            ],
            "$firstEmail,$lastEmail",
        ];
    }

    /**
     * @test
     * @dataProvider emailAddresses
     */
    public function itCanReturnItsValue(array $emailAddresses): void
    {
        $resource = Recipients::fromEmails(...$emailAddresses);

        $this->assertSame($emailAddresses, $resource->list());
    }

    /**
     * @test
     * @dataProvider emailAddresses
     */
    public function itCanBeReturnedAsAString(array $emailAddresses, string $expectedValue): void
    {
        $resource = Recipients::fromEmails(...$emailAddresses);

        $this->assertSame($expectedValue, $resource->toString());
    }

    /**
     * @test
     * @dataProvider emailAddresses
     */
    public function itCanBeCastedToAString(array $emailAddresses, string $expectedValue): void
    {
        $resource = Recipients::fromEmails(...$emailAddresses);

        $this->assertSame($expectedValue, (string) $resource);
    }
}
