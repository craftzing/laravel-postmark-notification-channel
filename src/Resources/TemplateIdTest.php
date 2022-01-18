<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use PHPUnit\Framework\TestCase;

use function random_int;

final class TemplateIdTest extends TestCase
{
    /**
     * @test
     */
    public function itImplementsTheTemplateIdentifierInterface(): void
    {
        $resource = new TemplateId(random_int(1, 10000));

        $this->assertInstanceOf(TemplateIdentifier::class, $resource);
    }

    /**
     * @test
     */
    public function itCanReturnItsValue(): void
    {
        $resource = new TemplateId($value = random_int(1, 10000));

        $this->assertSame($value, $resource->get());
    }

    /**
     * @test
     */
    public function itCanReturnItsValueAsAString(): void
    {
        $resource = new TemplateId($value = random_int(1, 10000));

        $this->assertSame((string) $value, $resource->toString());
    }

    /**
     * @test
     */
    public function itCanBeCastedToAString(): void
    {
        $resource = new TemplateId($value = random_int(1, 10000));

        $this->assertSame((string) $value, (string) $resource);
    }
}
