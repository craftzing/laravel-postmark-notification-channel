<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark\Resources;

use PHPUnit\Framework\TestCase;

final class DynamicTemplateModelTest extends TestCase
{
    /**
     * @test
     */
    public function itImplementsTheTemplateModelInterface(): void
    {
        $resource = new DynamicTemplateModel([]);

        $this->assertInstanceOf(TemplateModel::class, $resource);
    }

    /**
     * @test
     */
    public function itCanReturnItsAttributes(): void
    {
        $attributes = ['key' => 'some-value'];
        $resource = new DynamicTemplateModel($attributes);

        $this->assertSame($attributes, $resource->attributes());
    }

    /**
     * @test
     */
    public function itCanSetAttributesAfterInitialisation(): void
    {
        $immutableResource = new DynamicTemplateModel([]);

        $resource = $immutableResource->set('foo', 'bar');

        $this->assertSame([], $immutableResource->attributes());
        $this->assertSame(['foo' => 'bar'], $resource->attributes());
    }

    /**
     * @test
     */
    public function itCanOverwriteAttributesAfterInitialisation(): void
    {
        $attributes = ['foo' => 'bar'];
        $immutableResource = new DynamicTemplateModel($attributes);

        $resource = $immutableResource->set('foo', 'baz');

        $this->assertSame($attributes, $immutableResource->attributes());
        $this->assertSame(['foo' => 'baz'], $resource->attributes());
    }
}
