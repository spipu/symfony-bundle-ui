<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\DataProvider;

use DateTimeImmutable;
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
use Spipu\UiBundle\Tests\ResourceMock;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Tests\Unit\Service\Ui\AbstractTestCase;
use Spipu\UiBundle\Tests\Unit\Service\Ui\PersonalDataProtectionTest;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Doctrine::class)]
class DoctrineTest extends AbstractTestCase
{
    public function testValidateWrongMissingAll(): void
    {
        $containerMock = $this->getContainerMock();

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            PersonalDataProtectionTest::getService()
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
            PersonalDataProtectionTest::getService()
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
            PersonalDataProtectionTest::getService()
        );
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testClone(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service = new Doctrine(
            $containerMock->get('doctrine.orm.default_entity_manager'),
            PersonalDataProtectionTest::getService()
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
        $service = new Doctrine($entityManager, PersonalDataProtectionTest::getService());
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
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $filters = ['foo' => 'bar'];
        $requestMock
            ->expects($this->any())
            ->method('getFilters')
            ->willreturn($filters);

        $entityManager = SymfonyMock::getEntityManager($this);
        $service =  new Doctrine($entityManager, PersonalDataProtectionTest::getService());
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

        $this->assertPersonalDataCondition($result);
        $this->assertSame('%foo%', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataExactFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getFilter()->useExactValue();

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => 'foo']);

        $this->assertPersonalDataCondition($result);
        $this->assertSame('foo', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataSelectFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getType()->setType(ColumnType::TYPE_SELECT);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => 'foo']);

        $this->assertPersonalDataCondition($result);
        $this->assertSame('foo', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataRangeFilter(): void
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->getColumn('field_a_b')->getFilter()->useRange();

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => ['from' => '1']]);

        $this->assertPersonalDataCondition($result);
        $this->assertSame('1', $result['parameters'][':field_a_b_from']);

        $result = $this->preparePersonalDataQuery($definition, ['field_a_b' => []]);

        $this->assertArrayNotHasKey(':personal_data_now', $result['parameters']);
        $this->assertNull($result['where']);
    }

    public function testPersonalDataQuickSearch(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            [],
            'field_a_b',
            'foo'
        );

        $this->assertPersonalDataCondition($result);
        $this->assertSame('foo%', $result['parameters'][':field_a_b']);
    }

    public function testPersonalDataNotProtectedField(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getPersonalDataGridDefinitionMock(),
            ['field_b_a' => 'foo']
        );

        $this->assertSame([':field_b_a' => '%foo%'], $result['parameters']);
        $this->assertStringNotContainsString('personalDataHiddenAt', $result['where']);
    }

    public function testPersonalDataNotProtectedEntity(): void
    {
        $result = $this->preparePersonalDataQuery(
            SpipuUiMock::getGridDefinitionMock(ResourceMock::class),
            ['field_a_b' => 'foo'],
            'field_a_b',
            'bar'
        );

        $this->assertArrayNotHasKey(':personal_data_now', $result['parameters']);
        $this->assertStringNotContainsString('personalDataHiddenAt', $result['where']);
    }

    public function testPersonalDataParameterNameFilterCollision(): void
    {
        $result = $this->preparePersonalDataQuery(
            $this->getPersonalDataCollisionDefinition(),
            ['personal_data_now' => 'foo', 'field_a_b' => 'bar']
        );

        $this->assertPersonalDataParameterNameCollision($result);
    }

    public function testPersonalDataParameterNameQuickSearchCollision(): void
    {
        $result = $this->preparePersonalDataQuery(
            $this->getPersonalDataCollisionDefinition(),
            ['personal_data_now' => 'foo'],
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
            ['main.fieldAA = :personal_data_now']
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $result['parameters'][':personal_data_now_']);
        $this->assertArrayNotHasKey(':personal_data_now', $result['parameters']);
    }

    private function getPersonalDataCollisionDefinition(): GridDefinitionInterface
    {
        $definition = SpipuUiMock::getPersonalDataGridDefinitionMock();
        $definition->getDefinition()->addColumn(
            (new Column('personal_data_now', 'Now', 'fieldBA', 40))->setFilter(new ColumnFilter(true))
        );

        return $definition;
    }

    private function assertPersonalDataParameterNameCollision(array $result): void
    {
        $this->assertSame('%foo%', $result['parameters'][':personal_data_now']);
        $this->assertInstanceOf(DateTimeImmutable::class, $result['parameters'][':personal_data_now_']);
        $this->assertStringContainsString('main.personalDataHiddenAt > :personal_data_now_)', $result['where']);
    }

    private function assertPersonalDataCondition(array $result): void
    {
        $this->assertStringContainsString(
            'main.personalDataHiddenAt IS NULL OR main.personalDataHiddenAt > :personal_data_now',
            $result['where']
        );
        $this->assertInstanceOf(DateTimeImmutable::class, $result['parameters'][':personal_data_now']);
    }

    private function preparePersonalDataQuery(
        GridDefinitionInterface $definition,
        array $filters,
        ?string $quickSearchField = null,
        ?string $quickSearchValue = null,
        array $conditions = []
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

        $service = new Doctrine($entityManager, PersonalDataProtectionTest::getService());
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        foreach ($conditions as $condition) {
            $service->addCondition($condition);
        }
        $service->prepareQueryBuilder();

        return $result;
    }
}
