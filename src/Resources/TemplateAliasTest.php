<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use PHPUnit\Framework\TestCase;

final class TemplateAliasTest extends TestCase
{
    /**
     * @test
     */
    public function itImplementsTheTemplateIdentifierInterface(): void
    {
        $resource = new TemplateAlias('some-alias');

        $this->assertInstanceOf(TemplateIdentifier::class, $resource);
    }

    /**
     * @test
     */
    public function itCanReturnItsValue(): void
    {
        $resource = new TemplateAlias($value = 'some-alias');

        $this->assertSame($value, $resource->get());
    }

    /**
     * @test
     */
    public function itCanReturnItsValueAsAString(): void
    {
        $resource = new TemplateAlias($value = 'some-alias');

        $this->assertSame($value, $resource->toString());
    }

    /**
     * @test
     */
    public function itCanBeCastedToAString(): void
    {
        $resource = new TemplateAlias($value = 'some-alias');

        $this->assertSame($value, (string) $resource);
    }
}
