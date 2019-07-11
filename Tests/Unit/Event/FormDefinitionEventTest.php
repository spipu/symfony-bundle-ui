<?php
namespace Spipu\UiBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormDefinitionEvent;

class FormDefinitionEventTest extends TestCase
{
    public function testEvent()
    {
        $definition = new Form('test');

        $event = new FormDefinitionEvent($definition);

        $this->assertSame($definition, $event->getFormDefinition());
        $this->assertSame('spipu.ui.form.definition.test', $event->getEventCode());
    }
}
