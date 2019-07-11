<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Form;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\YesNo;
use Symfony\Component\Form\Extension\Core\Type;

class FieldSetTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Form\FieldSet('code', 'name', 10);

        $this->assertSame('code', $entity->getCode());
        $this->assertSame('name', $entity->getName());
        $this->assertSame(10, $entity->getPosition());

        $entity->setName('test');
        $this->assertSame('test', $entity->getName());
        
        $this->assertSame(false, $entity->isHiddenInForm());
        $entity->useHiddenInForm();
        $this->assertSame(true, $entity->isHiddenInForm());
        $entity->useHiddenInForm(false);
        $this->assertSame(false, $entity->isHiddenInForm());

        $this->assertSame(false, $entity->isHiddenInView());
        $entity->useHiddenInView();
        $this->assertSame(true, $entity->isHiddenInView());
        $entity->useHiddenInView(false);
        $this->assertSame(false, $entity->isHiddenInView());

        $this->assertSame('col-12', $entity->getCssClass());
        $entity->setCssClass('col-xs-12 col-md-6');
        $this->assertSame('col-xs-12 col-md-6', $entity->getCssClass());
    }
    
    public function testEntityFields()
    {
        $entity = new Form\FieldSet('code', 'name', 10);
        
        $fieldA = new Form\Field('code_a', 'text', 10, []);
        $fieldB = new Form\Field('code_b', 'text', 30, []);
        $fieldC = new Form\Field('code_c', 'text', 20, []);
        $fieldD = new Form\Field('code_d', 'text', 40, []);

        $entity->addField($fieldA);
        $entity->addField($fieldB);
        $entity->addField($fieldC);
        $entity->addField($fieldD);

        $this->assertSame($fieldA, $entity->getField($fieldA->getCode()));
        $this->assertSame($fieldB, $entity->getField($fieldB->getCode()));
        $this->assertSame($fieldC, $entity->getField($fieldC->getCode()));
        $this->assertSame($fieldD, $entity->getField($fieldD->getCode()));
        $this->assertSame(null, $entity->getField('code_wrong'));
        $this->assertSame(
            [
                $fieldA->getCode() => $fieldA,
                $fieldB->getCode() => $fieldB,
                $fieldC->getCode() => $fieldC,
                $fieldD->getCode() => $fieldD,
            ],
            $entity->getFields()
        );

        $entity->removeField('code_wrong');
        $entity->removeField($fieldD->getCode());
        $this->assertSame(null, $entity->getField($fieldD->getCode()));
        $this->assertSame(
            [
                $fieldA->getCode() => $fieldA,
                $fieldB->getCode() => $fieldB,
                $fieldC->getCode() => $fieldC,
            ],
            $entity->getFields()
        );

        $entity->prepareSort();

        $this->assertSame(
            [
                $fieldA->getCode() => $fieldA,
                $fieldC->getCode() => $fieldC,
                $fieldB->getCode() => $fieldB,
            ],
            $entity->getFields()
        );
    }
}
