<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity\Form;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Form\FieldConstraint::class)]
class FieldConstraintTest extends TestCase
{
    public function testEntity(): void
    {
        $entity = new Form\FieldConstraint('field1', 'field2', 'value');
        $this->assertSame('field1', $entity->getCode());
        $this->assertSame('field2', $entity->getFieldCode());
        $this->assertSame('value', $entity->getFieldValue());
    }
}
