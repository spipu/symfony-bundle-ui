<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Menu;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\Definition;
use Spipu\UiBundle\Service\Menu\DefinitionInterface;

class DefinitionTest extends TestCase
{
    public function testService()
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
