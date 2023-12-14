<?php
namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

class PositionTest extends TestCase
{
    public function testEntity()
    {
        $entity = new PositionEntity(10);
        $this->assertSame(10, $entity->getPosition());
    }
}

class PositionEntity implements PositionInterface
{
    use PositionTrait;

    public function __construct(int $position)
    {
        $this->setPosition($position);
    }
}
