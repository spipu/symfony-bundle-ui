<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;

class ActionTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Grid\Action('code', 'name', 10, 'route', ['key' => 'value']);

        $this->assertSame('code', $entity->getCode());
        $this->assertSame('name', $entity->getName());
        $this->assertSame(10, $entity->getPosition());
        $this->assertSame('route', $entity->getRouteName());
        $this->assertSame(['key' => 'value'], $entity->getRouteParams());
        $this->assertSame(null, $entity->getCssClass());
        $this->assertSame(null, $entity->getNeededRole());
        $this->assertSame(null, $entity->getIcon());
        $this->assertSame([], $entity->getConditions());

        $entity
            ->setCssClass('css')
            ->setNeededRole('role')
            ->setIcon('icon')
            ->setConditions(['id' => 1]);

        $this->assertSame('css', $entity->getCssClass());
        $this->assertSame('role', $entity->getNeededRole());
        $this->assertSame('icon', $entity->getIcon());
        $this->assertSame(['id' => 1], $entity->getConditions());
    }
}
