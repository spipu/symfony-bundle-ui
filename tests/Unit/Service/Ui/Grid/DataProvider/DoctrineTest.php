<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\DataProvider;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Tests\Unit\Service\Ui\AbstractTestCase;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Doctrine::class)]
class DoctrineTest extends AbstractTestCase
{
    public function testValidateWrongMissingAll(): void
    {
        $containerMock = $this->getContainerMock();

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingRequest(): void
    {
        $containerMock = $this->getContainerMock();
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));
        $service->setGridDefinition($definition->getDefinition());

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingDefinition(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testClone(): void
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));
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
        $service = new Doctrine($entityManager);
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
        $service =  new Doctrine($entityManager);
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
}
