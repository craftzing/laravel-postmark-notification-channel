<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use Craftzing\Laravel\NotificationChannels\Postmark\Testing\Concerns\WithFaker;
use PHPUnit\Framework\TestCase;

final class SenderTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function itCanBeInitialisedFromAnEmailAddress(): void
    {
        $resource = Sender::fromEmail($this->faker->email);

        $this->assertInstanceOf(Sender::class, $resource);
    }

    /**
     * @test
     */
    public function itCanBeReturnedAsAString(): void
    {
        $email = $this->faker->email;
        $resource = Sender::fromEmail($email);

        $this->assertSame($email, $resource->toString());
    }

    /**
     * @test
     */
    public function itCanBeCastedToAString(): void
    {
        $email = $this->faker->email;
        $resource = Sender::fromEmail($email);

        $this->assertSame($email, (string) $resource);
    }

    /**
     * @test
     */
    public function itAcceptsAnOptionalName(): void
    {
        $email = $this->faker->email;
        $name = $this->faker->name;
        $immutableResource = Sender::fromEmail($email);

        $resource = $immutableResource->as($name);

        $this->assertInstanceOf(Sender::class, $resource);
        $this->assertSame("$name <$email>", $resource->toString());

        // Note that we should ensure the original resource did not change as it should be immutable...
        $this->assertSame($email, $immutableResource->toString());
    }
}
