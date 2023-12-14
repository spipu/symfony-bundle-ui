<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\DataProvider;

use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Tests\Unit\Service\Ui\AbstractTest;

class DoctrineTest extends AbstractTest
{
    public function testValidateWrongMissingAll()
    {
        $containerMock = $this->getContainerMock();

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingRequest()
    {
        $containerMock = $this->getContainerMock();
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));
        $service->setGridDefinition($definition->getDefinition());

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingDefinition()
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);

        $service =  new Doctrine($containerMock->get('doctrine.orm.default_entity_manager'));
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testClone()
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

    public function testValidateOk()
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

        $newFilters = ['new' => true];
        $service->forceFilters($newFilters);
        $this->assertSame($newFilters, $service->getFilters());

        $service->resetDataProvider();
        $this->assertSame($filters, $service->getFilters());

        $service->addMappingValue('foo', 'bar', ['bar1', 'bar2']);
        $this->assertSame('value1', $service->applyMappingValue('field', 'value1'));
        $this->assertSame('value2', $service->applyMappingValue('foo', 'value2'));
        $this->assertSame(['bar1', 'bar2'], $service->applyMappingValue('foo', 'bar'));
    }
}
