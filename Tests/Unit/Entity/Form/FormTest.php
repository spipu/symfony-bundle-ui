<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Form;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;

class FormTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Form\Form('code', 'entity_name');
        $this->assertSame('code', $entity->getCode());
        $this->assertSame('entity_name', $entity->getEntityClassName());

        $entity = new Form\Form('code');

        $this->assertSame('code', $entity->getCode());
        $entity->setCode('test');
        $this->assertSame('test', $entity->getCode());

        $this->assertSame('@SpipuUi/entity/view.html.twig', $entity->getTemplateView());
        $entity->setTemplateView('test_view.html.twig');
        $this->assertSame('test_view.html.twig', $entity->getTemplateView());

        $this->assertSame('@SpipuUi/entity/form.html.twig', $entity->getTemplateForm());
        $entity->setTemplateForm('test_form.html.twig');
        $this->assertSame('test_form.html.twig', $entity->getTemplateForm());

        $this->assertSame(null, $entity->getEntityClassName());
        $entity->setEntityClassName('entity_name');
        $this->assertSame('entity_name', $entity->getEntityClassName());
    }

    public function testEntityFieldSets()
    {
        $entity = new Form\Form('code');

        $fieldSetA = new Form\FieldSet('code_a', 'name_a', 10);
        $fieldSetB = new Form\FieldSet('code_b', 'name_b', 30);
        $fieldSetC = new Form\FieldSet('code_c', 'name_c', 20);
        $fieldSetD = new Form\FieldSet('code_d', 'name_d', 40);

        $entity->addFieldSet($fieldSetA);
        $entity->addFieldSet($fieldSetB);
        $entity->addFieldSet($fieldSetC);
        $entity->addFieldSet($fieldSetD);

        $this->assertSame($fieldSetA, $entity->getFieldSet($fieldSetA->getCode()));
        $this->assertSame($fieldSetB, $entity->getFieldSet($fieldSetB->getCode()));
        $this->assertSame($fieldSetC, $entity->getFieldSet($fieldSetC->getCode()));
        $this->assertSame($fieldSetD, $entity->getFieldSet($fieldSetD->getCode()));
        $this->assertSame(null, $entity->getFieldSet('code_wrong'));
        $this->assertSame(
            [
                $fieldSetA->getCode() => $fieldSetA,
                $fieldSetB->getCode() => $fieldSetB,
                $fieldSetC->getCode() => $fieldSetC,
                $fieldSetD->getCode() => $fieldSetD,
            ],
            $entity->getFieldSets()
        );

        $entity->removeFieldSet('code_wrong');
        $entity->removeFieldSet($fieldSetD->getCode());
        $this->assertSame(null, $entity->getFieldSet($fieldSetD->getCode()));
        $this->assertSame(
            [
                $fieldSetA->getCode() => $fieldSetA,
                $fieldSetB->getCode() => $fieldSetB,
                $fieldSetC->getCode() => $fieldSetC,
            ],
            $entity->getFieldSets()
        );

        $entity->prepareSort();

        $this->assertSame(
            [
                $fieldSetA->getCode() => $fieldSetA,
                $fieldSetC->getCode() => $fieldSetC,
                $fieldSetB->getCode() => $fieldSetB,
            ],
            $entity->getFieldSets()
        );
    }
}
