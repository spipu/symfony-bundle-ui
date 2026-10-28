<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\PersonalData;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Exception\UiException;
use Spipu\UiBundle\Service\PersonalData\PersonalDataProtection;
use Spipu\UiBundle\Tests\PersonalDataResourceMock;
use Spipu\UiBundle\Tests\PersonalDataRuleMock;
use Spipu\UiBundle\Tests\PersonalDataSubResourceMock;
use Spipu\UiBundle\Tests\ResourceMock;
use Spipu\UiBundle\Tests\SpipuUiMock;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PersonalDataProtection::class)]
class PersonalDataProtectionTest extends TestCase
{
    private function getEntity(bool $hidden, bool $subHidden): PersonalDataResourceMock
    {
        $sub = new PersonalDataSubResourceMock();
        $sub->setHidden($subHidden);
        $sub->setFieldBA('sub_ba');

        $entity = SpipuUiMock::getPersonalDataResourceMock();
        $entity
            ->setSub($sub)
            ->setHidden($hidden);
        $entity
            ->setFieldAA(42)
            ->setFieldAB('secret');

        return $entity;
    }

    private function getConditions(
        PersonalDataProtection $service,
        string $entityClass,
        string $path,
        array $excludedAliases = []
    ): array {
        $index = 0;

        return $service->getVisibleDqlConditions(
            $entityClass,
            $path,
            $excludedAliases,
            function () use (&$index): string {
                return ':p' . $index++;
            }
        );
    }

    public function testDuplicateRule(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection(
            $this,
            [
                new PersonalDataRuleMock(PersonalDataResourceMock::class, ['fieldAB']),
                new PersonalDataRuleMock(PersonalDataResourceMock::class, ['fieldBA']),
            ]
        );

        $this->expectException(UiException::class);
        $service->isEntityHidden(SpipuUiMock::getPersonalDataResourceMock());
    }

    public function testGetVisibleDqlConditions(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $this->assertSame([], $this->getConditions($service, PersonalDataResourceMock::class, 'fieldAA'));
        $this->assertSame([], $this->getConditions($service, ResourceMock::class, 'fieldAB'));

        $this->assertSame(
            ['main' => ['main.hidden = :p0 OR main.fieldAA > :p1', [':p0' => false, ':p1' => 10]]],
            $this->getConditions($service, PersonalDataResourceMock::class, 'fieldAB')
        );
        $this->assertSame(
            ['sub' => ['sub.hidden = :p0 OR sub.fieldAA > :p1', [':p0' => false, ':p1' => 10]]],
            $this->getConditions($service, PersonalDataResourceMock::class, 'sub.fieldBA')
        );

        $this->assertSame([], $this->getConditions($service, PersonalDataResourceMock::class, 'fieldAB', ['main']));
        $this->assertSame([], $this->getConditions($service, PersonalDataResourceMock::class, 'sub.fieldAB'));
        $this->assertSame([], $this->getConditions($service, PersonalDataResourceMock::class, 'wrong.fieldBA'));
        $this->assertSame([], $this->getConditions($service, 'stdClass', 'sub.fieldBA'));
    }

    public function testGetVisibleDqlConditionsProtectedAssociation(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection(
            $this,
            [
                new PersonalDataRuleMock(PersonalDataResourceMock::class, ['sub']),
                new PersonalDataRuleMock(PersonalDataSubResourceMock::class, ['fieldBA']),
            ]
        );

        $this->assertSame(
            ['main', 'sub'],
            array_keys($this->getConditions($service, PersonalDataResourceMock::class, 'sub.fieldBA'))
        );
        $this->assertSame(
            ['sub'],
            array_keys($this->getConditions($service, PersonalDataResourceMock::class, 'sub.fieldBA', ['main']))
        );
        $this->assertSame(
            ['main'],
            array_keys($this->getConditions($service, PersonalDataResourceMock::class, 'sub.fieldBB'))
        );

        $entity = $this->getEntity(true, false);
        $this->assertTrue($service->isHidden($entity, 'sub.fieldBB'));
    }

    public function testInactiveRule(): void
    {
        $rule = new PersonalDataRuleMock(PersonalDataResourceMock::class, ['fieldAB']);
        $service = SpipuUiMock::getPersonalDataProtection($this, [$rule]);

        $entity = $this->getEntity(true, false);
        $this->assertTrue($service->isEntityHidden($entity));
        $this->assertTrue($service->isHidden($entity, 'fieldAB'));
        $this->assertCount(1, $this->getConditions($service, PersonalDataResourceMock::class, 'fieldAB'));

        $rule->setActive(false);
        $this->assertTrue($service->isEntityHidden($entity), 'The active rule is cached');

        $service->reset();
        $this->assertFalse($service->isEntityHidden($entity));
        $this->assertFalse($service->isHidden($entity, 'fieldAB'));
        $this->assertSame([], $this->getConditions($service, PersonalDataResourceMock::class, 'fieldAB'));
    }

    public function testHiddenEntityCache(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $entity = $this->getEntity(false, false);
        $this->assertFalse($service->isEntityHidden($entity));

        $entity->setHidden(true);
        $this->assertFalse($service->isEntityHidden($entity), 'The hidden state is cached for the same entity');
        $this->assertFalse($service->isHidden($entity, 'fieldAB'), 'The field check uses the same cache');

        $other = $this->getEntity(true, false);
        $this->assertTrue($service->isEntityHidden($other), 'The cache is per entity id');

        $service->reset();
        $this->assertTrue($service->isEntityHidden($entity));
        $this->assertTrue($service->isHidden($entity, 'fieldAB'));
    }

    public function testEntityWithoutId(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $entity = $this->getEntity(true, false);
        $entity->setId(null);
        $this->assertFalse($service->isEntityHidden($entity));
        $this->assertFalse($service->isHidden($entity, 'fieldAB'));
    }

    public function testRuleOfChildClass(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $child = new class extends PersonalDataResourceMock {
        };
        $this->assertFalse($service->isEntityHidden($child));

        $child = clone $child;
        $child->setHidden(true);
        $this->assertTrue($service->isEntityHidden($child));
    }

    public function testIsEntityHidden(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $this->assertTrue($service->isEntityHidden($this->getEntity(true, false)));
        $this->assertFalse($service->isEntityHidden($this->getEntity(false, true)));
        $this->assertFalse($service->isEntityHidden(SpipuUiMock::getResourceMock()));
    }

    public function testIsHidden(): void
    {
        $service = SpipuUiMock::getPersonalDataProtection($this);

        $entity = $this->getEntity(true, false);
        $this->assertTrue($service->isHidden($entity, 'fieldAB'));
        $this->assertFalse($service->isHidden($entity, 'fieldAA'));
        $this->assertFalse($service->isHidden($entity, 'sub.fieldBA'));

        $entity = $this->getEntity(false, true);
        $this->assertFalse($service->isHidden($entity, 'fieldAB'));
        $this->assertTrue($service->isHidden($entity, 'sub.fieldBA'));
        $this->assertFalse($service->isHidden($entity, 'sub.fieldAB'));

        $this->assertFalse($service->isHidden(SpipuUiMock::getResourceMock()->setFieldAB('value'), 'fieldAB'));
    }

    public function testIsHiddenNullSubObject(): void
    {
        $entity = $this->getEntity(false, false);
        $entity->setSub(null);

        $this->assertFalse(SpipuUiMock::getPersonalDataProtection($this)->isHidden($entity, 'sub.fieldBA'));
    }

    public function testIsHiddenWrongSubObject(): void
    {
        $this->expectException(GridException::class);
        SpipuUiMock::getPersonalDataProtection($this)->isHidden($this->getEntity(false, false), 'wrongSub.fieldBA');
    }

    public function testIsHiddenScalarSubObject(): void
    {
        $this->expectException(GridException::class);
        SpipuUiMock::getPersonalDataProtection($this)->isHidden($this->getEntity(false, false), 'fieldAA.fieldBA');
    }
}
