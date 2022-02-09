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
        $resource = DynamicTemplateModel::fromVariables([]);

        $this->assertInstanceOf(TemplateModel::class, $resource);
    }

    /**
     * @test
     */
    public function itCanReturnItsVariables(): void
    {
        $attributes = ['key' => 'some-value'];
        $resource = DynamicTemplateModel::fromVariables($attributes);

        $this->assertSame($attributes, $resource->variables());
    }

    /**
     * @test
     */
    public function itCanSetVariablesAfterInitialisation(): void
    {
        $immutableResource = DynamicTemplateModel::fromVariables([]);

        $resource = $immutableResource->set('foo', 'bar');

        $this->assertSame([], $immutableResource->variables());
        $this->assertSame(['foo' => 'bar'], $resource->variables());
    }

    /**
     * @test
     */
    public function itCanOverwriteVariablesAfterInitialisation(): void
    {
        $attributes = ['foo' => 'bar'];
        $immutableResource = DynamicTemplateModel::fromVariables($attributes);

        $resource = $immutableResource->set('foo', 'baz');

        $this->assertSame($attributes, $immutableResource->variables());
        $this->assertSame(['foo' => 'baz'], $resource->variables());
    }
}
