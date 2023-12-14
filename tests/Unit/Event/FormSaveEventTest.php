<?php
namespace Spipu\UiBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormSaveEvent;
use Symfony\Component\Form\FormInterface;

class FormSaveEventTest extends TestCase
{
    public function testEvent()
    {
        $definition = new Form('test');
        $form = $this->createMock(FormInterface::class);
        $entity = $this->createMock(EntityInterface::class);

        $event = new FormSaveEvent($definition, $form, $entity);

        $this->assertSame($definition, $event->getFormDefinition());
        $this->assertSame($form, $event->getForm());
        $this->assertSame($entity, $event->getResource());
        $this->assertSame('spipu.ui.form.save.test', $event->getEventCode());
    }
}
