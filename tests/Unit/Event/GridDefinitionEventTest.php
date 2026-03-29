<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Event\GridDefinitionEvent;

class GridDefinitionEventTest extends TestCase
{
    public function testEvent(): void
    {
        $definition = new Grid('test');

        $event = new GridDefinitionEvent($definition);

        $this->assertSame($definition, $event->getGridDefinition());
        $this->assertSame('spipu.ui.grid.definition.test', $event->getEventCode());
    }
}
