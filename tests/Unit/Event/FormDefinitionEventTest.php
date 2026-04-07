<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormDefinitionEvent;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(FormDefinitionEvent::class)]
class FormDefinitionEventTest extends TestCase
{
    public function testEvent(): void
    {
        $definition = new Form('test');

        $event = new FormDefinitionEvent($definition);

        $this->assertSame($definition, $event->getFormDefinition());
        $this->assertSame('spipu.ui.form.definition.test', $event->getEventCode());
    }
}
