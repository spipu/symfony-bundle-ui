<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\Ui;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\PersonalDataProtection;
use Spipu\UiBundle\Tests\PersonalDataResourceMock;
use Spipu\UiBundle\Tests\ResourceMock;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\PropertyAccess\PropertyAccess;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PersonalDataProtection::class)]
class PersonalDataProtectionTest extends TestCase
{
    public static function getService(): PersonalDataProtection
    {
        return new PersonalDataProtection(PropertyAccess::createPropertyAccessor());
    }

    private function getEntity(?DateTimeImmutable $hiddenAt): PersonalDataResourceMock
    {
        $sub = SpipuUiMock::getResourceMock();
        $sub->setFieldBA('sub_ba');

        $entity = SpipuUiMock::getPersonalDataResourceMock();
        $entity
            ->setSub($sub)
            ->setPersonalDataHiddenAt($hiddenAt);
        $entity
            ->setFieldAA(42)
            ->setFieldAB('secret');

        return $entity;
    }

    public function testIsProtectedField(): void
    {
        $service = self::getService();

        $this->assertFalse($service->isProtectedField(null, 'fieldAB'));
        $this->assertFalse($service->isProtectedField(ResourceMock::class, 'fieldAB'));
        $this->assertTrue($service->isProtectedField(PersonalDataResourceMock::class, 'fieldAB'));
        $this->assertTrue($service->isProtectedField(PersonalDataResourceMock::class, 'sub.fieldBA'));
        $this->assertFalse($service->isProtectedField(PersonalDataResourceMock::class, 'fieldAA'));
        $this->assertFalse($service->isProtectedField(PersonalDataResourceMock::class, 'fieldBA'));
        $this->assertFalse($service->isProtectedField(PersonalDataResourceMock::class, 'sub.fieldBB'));
    }

    public function testGetHiddenAtField(): void
    {
        $this->assertSame(
            'personalDataHiddenAt',
            self::getService()->getHiddenAtField(PersonalDataResourceMock::class)
        );
    }

    public function testIsHidden(): void
    {
        $service = self::getService();

        $hidden = $this->getEntity(new DateTimeImmutable('-1 day'));
        $this->assertTrue($service->isHidden($hidden, 'fieldAB'));
        $this->assertTrue($service->isHidden($hidden, 'sub.fieldBA'));
        $this->assertFalse($service->isHidden($hidden, 'fieldAA'));

        $this->assertFalse($service->isHidden($this->getEntity(null), 'fieldAB'));
        $this->assertFalse($service->isHidden($this->getEntity(new DateTimeImmutable('+1 day')), 'fieldAB'));

        $this->assertFalse($service->isHidden(SpipuUiMock::getResourceMock()->setFieldAB('value'), 'fieldAB'));
    }

    public function testGetValueHidden(): void
    {
        $service = self::getService();
        $entity = $this->getEntity(new DateTimeImmutable('-1 day'));

        $this->assertNull($service->getValue($entity, 'fieldAB'));
        $this->assertNull($service->getValue($entity, 'sub.fieldBA'));
        $this->assertSame(42, $service->getValue($entity, 'fieldAA'));
    }

    public function testGetValueNotHidden(): void
    {
        $service = self::getService();
        $entity = $this->getEntity(null);

        $this->assertSame('secret', $service->getValue($entity, 'fieldAB'));
        $this->assertSame('sub_ba', $service->getValue($entity, 'sub.fieldBA'));
        $this->assertSame(42, $service->getValue($entity, 'fieldAA'));
    }

    public function testGetValueNullSubObject(): void
    {
        $entity = $this->getEntity(null);
        $entity->setSub(null);

        $this->assertNull(self::getService()->getValue($entity, 'sub.fieldBA'));
    }

    public function testGetValueWrongSubField(): void
    {
        $this->expectException(GridException::class);
        self::getService()->getValue($this->getEntity(null), 'sub.wrongField');
    }

    public function testGetValueWrongSubObject(): void
    {
        $this->expectException(GridException::class);
        self::getService()->getValue($this->getEntity(null), 'wrongSub.fieldBA');
    }

    public function testGetValueScalarSubObject(): void
    {
        $this->expectException(GridException::class);
        self::getService()->getValue($this->getEntity(null), 'fieldAA.fieldBA');
    }

    public function testGetValueNotProtectedEntity(): void
    {
        $service = self::getService();
        $entity = SpipuUiMock::getResourceMock()->setFieldAA(7);

        $this->assertSame(7, $service->getValue($entity, 'fieldAA'));
        $this->assertSame(7, $service->getValue($entity, 'getFieldAA'));

        $this->expectException(GridException::class);
        $service->getValue($entity, 'wrongField');
    }

    public function testDisableSort(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock()->getDefinition();

        self::getService()->disableSort($definition);

        $this->assertTrue($definition->getColumn('field_a_a')->isSortable());
        $this->assertFalse($definition->getColumn('field_a_b')->isSortable());
        $this->assertTrue($definition->getColumn('field_b_a')->isSortable());
    }

    public function testDisableSortNotProtectedEntity(): void
    {
        $definition = SpipuUiMock::getGridDefinitionMock()->getDefinition();

        self::getService()->disableSort($definition);

        $this->assertTrue($definition->getColumn('field_a_a')->isSortable());
        $this->assertTrue($definition->getColumn('field_a_b')->isSortable());
        $this->assertTrue($definition->getColumn('field_b_a')->isSortable());
    }
}
