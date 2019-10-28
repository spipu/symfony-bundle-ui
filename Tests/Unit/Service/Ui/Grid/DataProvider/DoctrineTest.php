<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\DataProvider;

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

        $service =  new Doctrine($containerMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingRequest()
    {
        $containerMock = $this->getContainerMock();
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service =  new Doctrine($containerMock);
        $service->setGridDefinition($definition->getDefinition());

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testValidateWrongMissingDefinition()
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);

        $service =  new Doctrine($containerMock);
        $service->setGridRequest($requestMock);

        $this->expectException(GridException::class);
        $service->validate();
    }

    public function testClone()
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $service =  new Doctrine($containerMock);
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        $this->assertTrue($service->validate());

        $clonedService = clone $service;
        $this->expectException(GridException::class);
        $clonedService->validate();
    }


    public function testValidateOk()
    {
        $containerMock = $this->getContainerMock();
        $requestMock   = $this->createMock(GridRequest::class);
        $definition    = SpipuUiMock::getGridDefinitionMock();

        $filters = ['foo' => 'bar'];
        $requestMock
            ->expects($this->once())
            ->method('getFilters')
            ->willreturn($filters);

        $service =  new Doctrine($containerMock);
        $service->setGridDefinition($definition->getDefinition());
        $service->setGridRequest($requestMock);
        $this->assertTrue($service->validate());
        $this->assertSame($definition->getDefinition(), $service->getDefinition());
        $this->assertSame($requestMock, $service->getRequest());

        $this->assertSame($filters, $service->getFilters());

        $newFilters = ['new' => true];
        $service->forceFilters($newFilters);
        $this->assertSame($newFilters, $service->getFilters());
    }
}
