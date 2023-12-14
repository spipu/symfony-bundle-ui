<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Form;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;

class FieldConstraintTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Form\FieldConstraint('field1', 'field2', 'value');
        $this->assertSame('field1', $entity->getCode());
        $this->assertSame('field2', $entity->getFieldCode());
        $this->assertSame('value', $entity->getFieldValue());
    }
}
