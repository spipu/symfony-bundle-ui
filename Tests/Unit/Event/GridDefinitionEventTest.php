<?php
namespace Spipu\UiBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Event\GridDefinitionEvent;

class GridDefinitionEventTest extends TestCase
{
    public function testEvent()
    {
        $definition = new Grid('test');

        $event = new GridDefinitionEvent($definition);

        $this->assertSame($definition, $event->getGridDefinition());
        $this->assertSame('spipu.ui.grid.definition.test', $event->getEventCode());
    }
}
