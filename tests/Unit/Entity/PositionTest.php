<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PositionTrait::class)]
class PositionTest extends TestCase
{
    public function testEntity(): void
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
