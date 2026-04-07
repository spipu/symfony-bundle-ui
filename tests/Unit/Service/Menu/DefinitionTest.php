<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\Menu;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\Definition;
use Spipu\UiBundle\Service\Menu\DefinitionInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Definition::class)]
class DefinitionTest extends TestCase
{
    public function testService(): void
    {
        $service = new Definition();
        $this->assertInstanceOf(DefinitionInterface::class, $service);

        $definition = $service->getDefinition();
        $this->assertSame($definition, $service->getDefinition());

        $this->assertInstanceOf(Item::class, $definition);
        $this->assertSame(null, $definition->getCode());
        $this->assertSame('Main', $definition->getName());
    }
}
