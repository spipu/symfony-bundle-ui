<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Grid\Column;
use Spipu\UiBundle\Entity\Grid\ColumnFilter;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Tests\PersonalDataResourceMock;
use Spipu\UiBundle\Tests\PersonalDataRuleMock;
use Spipu\UiBundle\Tests\PersonalDataSubResourceMock;
use Spipu\UiBundle\Tests\ResourceMock;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Tests\Unit\Service\Ui\AbstractTestCase;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Doctrine::class)]
class DoctrineTest extends AbstractTestCase
{
    public function testValidateWrongMissingAll(): void
    {
        $containerMock = $this->getContainerMock();

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            SpipuUiMock::getPersonalDataProtection($this)
        );

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingRequest(): void
    {
        $containerMock = $this->getContainerMock();
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            SpipuUiMock::getPersonalDataProtection($this)
        );
        $service->setGridDefinition($definition->getDefinition());

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingDefinition(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            SpipuUiMock::getPersonalDataProtection($this)
        );
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingEntityName(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            SpipuUiMock::getPersonalDataProtection($this)
        );
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $this->expectExceptionMessage('The grid definition has no entity name');
        $service->validate();
    }

    public function testClone(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock(ResourceMock::class);

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            SpipuUiMock::getPersonalDataProtection($this)
        );
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        $this->assertTrue($service->validate());

        $clonedService = clone $service;
        $this->expectException(GridException::class);
        $clonedService->validate();
    }

    public function testValueTransformer(): void
    {
        $definition = SpipuUiMock::getGridDefinitionMock();
        $grid = $definition->getDefinition();
        $requestMock = $this->createMock(GridRequest::class);

        $entityManager = SymfonyMock::getEntityManager($this);
        $service = new Doctrine($entityManager, SpipuUiMock::getPersonalDataProtection($this));
        $service->setGridDefinition($grid);
        $service->setGridRequest($requestMock);

        $column = $grid->getColumn('field_b_a');

        // Default: no transformer
        $this->assertNull($column->getFilter()->getValueTransformer());
        $this->assertSame('test', $service->applyValueTransformer($column, 'test'));

        // With transformer
        $column->getFilter()->setValueTransformer(fn(string $v): string => strtoupper($v));
        $this->assertSame('TEST', $service->applyValueTransformer($column, 'test'));

        // Replacement
        $column->getFilter()->setValueTransformer(fn(string $v): string => strrev($v));
        $this->assertSame('tset', $service->applyValueTransformer($column, 'test'));
    }

    public function testValidateOk(): void
    {
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock(ResourceMock::class);

        $filters = ['foo' => 'bar'];
        $requestMock
            ->expects($this->any())
            ->method('getFilters')
            ->willreturn($filters);

        $entityManager = SymfonyMock::getEntityManager($this);
        $service =  new Doctrine($entityManager, SpipuUiMock::getPersonalDataProtection($this));
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        $this->assertTrue($service->validate());
        $this->assertSame($definition->getDefinition(), $service->getDefinition());
        $this->assertSame($requestMock, $service->getRequest());

        $this->assertSame($filters, $service->getFilters());

        $service->forceFilters(['new' => true]);
        $this->assertSame(['new' => '1'], $service->getFilters());

        $service->resetDataProvider();
        $this->assertSame($filters, $service->getFilters());

        $service->addMappingValue('foo', 'bar', ['bar1', 'bar2']);
        $this->assertSame('value1', $service->applyMappingValue('field', 'value1'));
        $this->assertSame('value2', $service->applyMappingValue('foo', 'value2'));
        $this->assertSame(['bar1', 'bar2'], $service->applyMappingValue('foo', 'bar'));
    }

    public function testPersonalDataTextFilter(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_a_b' => 'foo']
        );

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame('%foo%', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataExactFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getFilter()->useExactValue();

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => 'foo']);

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame('foo', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataSelectFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getType()->setType(ColumnType::TYPE_SELECT);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => 'foo']);

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame('foo', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataRangeFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getFilter()->useRange();

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => ['from' => '1']]);

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame('1', $result['parameters'][':field_a_b_from']);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => []]);

        $this->assertSame([], $result['parameters']);
        $this->assertNull($result['where']);
    }

    public function testDateTimeRangeFilterToIncludesWholeDay(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $column = $definition->getDefinition()->getColumn('field_a_b');
        $column->setType(new ColumnType(ColumnType::TYPE_DATETIME));
        $column->getFilter()->useRange();

        $result = $this->preparePersonalDataQuery(
            $definition,
            ['field_a_b' => ['from' => '2026-10-27', 'to' => '2026-10-27']]
        );
        $this->assertStringContainsString('main.fieldAB >= :field_a_b_from', $result['where']);
        $this->assertStringContainsString('main.fieldAB < :field_a_b_to', $result['where']);
        $this->assertSame('2026-10-27', $result['parameters'][':field_a_b_from']);
        $this->assertSame('2026-10-28 00:00:00', $result['parameters'][':field_a_b_to']);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => ['to' => '2026-10-27 12:00:00']]);
        $this->assertStringContainsString('main.fieldAB <= :field_a_b_to', $result['where']);
        $this->assertSame('2026-10-27 12:00:00', $result['parameters'][':field_a_b_to']);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => ['to' => '2026-02-31']]);
        $this->assertStringContainsString('main.fieldAB <= :field_a_b_to', $result['where']);
        $this->assertSame('2026-02-31', $result['parameters'][':field_a_b_to']);

        $column->setType(new ColumnType(ColumnType::TYPE_DATE));
        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => ['to' => '2026-10-27']]);
        $this->assertStringContainsString('main.fieldAB <= :field_a_b_to', $result['where']);
        $this->assertSame('2026-10-27', $result['parameters'][':field_a_b_to']);
    }

    public function testPersonalDataQuickSearch(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            [],
            'field_a_b',
            'foo'
        );

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame('foo%', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataJoinedField(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['sub_field_b_a' => 'foo']
        );

        $this->assertPersonalDataCondition($result, 'sub');
        $this->assertStringNotContainsString('main.hidden', $result['where']);
        $this->assertSame('%foo%', $result['parameters'][':sub_field_b_a']);
    }

    public function testPersonalDataProtectedAssociation(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['sub_field_b_a' => 'foo'],
            null,
            null,
            [],
            [
                new PersonalDataRuleMock(PersonalDataResourceMock::class, ['sub']),
                new PersonalDataRuleMock(PersonalDataSubResourceMock::class, ['fieldBA']),
            ]
        );

        $this->assertStringContainsString(
            '(main.hidden = :personal_data_0 OR main.fieldAA > :personal_data_1)',
            $result['where']
        );
        $this->assertStringContainsString(
            '(sub.hidden = :personal_data_2 OR sub.fieldAA > :personal_data_3)',
            $result['where']
        );
    }

    public function testPersonalDataSeveralAliases(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_a_b' => 'foo', 'sub_field_b_a' => 'bar']
        );

        $this->assertStringContainsString(
            '(main.hidden = :personal_data_0 OR main.fieldAA > :personal_data_1)',
            $result['where']
        );
        $this->assertStringContainsString(
            '(sub.hidden = :personal_data_2 OR sub.fieldAA > :personal_data_3)',
            $result['where']
        );
        $this->assertFalse($result['parameters'][':personal_data_2']);
        $this->assertSame(10, $result['parameters'][':personal_data_3']);
    }

    public function testPersonalDataSameAliasOnce(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_a_b' => 'foo'],
            'field_a_b',
            'bar'
        );

        $this->assertPersonalDataCondition($result, 'main');
        $this->assertSame(1, substr_count($result['where'], 'main.hidden'));
        $this->assertArrayNotHasKey(':personal_data_2', $result['parameters']);
    }

    public function testPersonalDataNotProtectedField(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_b_a' => 'foo']
        );

        $this->assertSame([':field_b_a' => '%foo%'], $result['parameters']);
        $this->assertStringNotContainsString('hidden', $result['where']);
    }

    public function testPersonalDataNotProtectedEntity(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getGridDefinitionMock(ResourceMock::class),
            ['field_a_b' => 'foo'],
            'field_a_b',
            'bar'
        );

        $this->assertArrayNotHasKey(':personal_data_0', $result['parameters']);
        $this->assertStringNotContainsString('hidden', $result['where']);
    }

    public function testPersonalDataParameterNameFilterCollision(): void
    {
        $result = $this->preparePersonalDataQuery(
            $this->getPersonalDataCollisionDefinition(),
            ['personal_data_0' => 'foo', 'field_a_b' => 'bar']
        );

        $this->assertPersonalDataParameterNameCollision($result);
    }

    public function testPersonalDataParameterNameQuickSearchCollision(): void
    {
        $result = $this->preparePersonalDataQuery(
            $this->getPersonalDataCollisionDefinition(),
            ['personal_data_0' => 'foo'],
            'field_a_b',
            'bar'
        );

        $this->assertPersonalDataParameterNameCollision($result);
    }

    public function testPersonalDataParameterNameConditionCollision(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_a_b' => 'foo'],
            null,
            null,
            ['main.fieldAA = :personal_data_0']
        );

        $this->assertFalse($result['parameters'][':personal_data_0_']);
        $this->assertSame(10, $result['parameters'][':personal_data_1']);
        $this->assertArrayNotHasKey(':personal_data_0', $result['parameters']);
    }

    private function getPersonalDataCollisionDefinition(): GridDefinitionInterface
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->addColumn(
            (new Column('personal_data_0', 'Collision', 'fieldBA', 40))->setFilter(new ColumnFilter(true))
        );

        return $definition;
    }

    private function assertPersonalDataParameterNameCollision(array $result): void
    {
        $this->assertSame('%foo%', $result['parameters'][':personal_data_0']);
        $this->assertFalse($result['parameters'][':personal_data_0_']);
        $this->assertSame(10, $result['parameters'][':personal_data_1']);
        $this->assertStringContainsString(
            '(main.hidden = :personal_data_0_ OR main.fieldAA > :personal_data_1)',
            $result['where']
        );
    }

    private function assertPersonalDataCondition(array $result, string $alias): void
    {
        $this->assertStringContainsString(
            '(' . $alias . '.hidden = :personal_data_0 OR ' . $alias . '.fieldAA > :personal_data_1)',
            $result['where']
        );
        $this->assertFalse($result['parameters'][':personal_data_0']);
        $this->assertSame(10, $result['parameters'][':personal_data_1']);
    }

    private function preparePersonalDataQuery(
        GridDefinitionInterface $definition,
        array $filters,
        ?string $quickSearchField = null,
        ?string $quickSearchValue = null,
        array $conditions = [],
        ?array $rules = null
    ): array {
        $result = ['where' => null, 'parameters' => []];

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('expr')->willReturn(new Expr());
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder
            ->method('where')
            ->willReturnCallback(
                function (mixed $where) use (&$result, $queryBuilder): QueryBuilder {
                    $result['where'] = (string) $where;
                    return $queryBuilder;
                }
            );
        $queryBuilder
            ->method('setParameter')
            ->willReturnCallback(
                function (string $key, mixed $value) use (&$result, $queryBuilder): QueryBuilder {
                    $result['parameters'][$key] = $value;
                    return $queryBuilder;
                }
            );

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $requestMock = $this->createMock(GridRequest::class);
        $requestMock->method('getFilters')->willReturn($filters);
        $requestMock->method('getQuickSearchField')->willReturn($quickSearchField);
        $requestMock->method('getQuickSearchValue')->willReturn($quickSearchValue);

        $service = new Doctrine($entityManager, SpipuUiMock::getPersonalDataProtection($this, $rules));
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        foreach ($conditions as $condition) {
            $service->addCondition($condition);
        }
        $service->prepareQueryBuilder();

        return $result;
    }
}
