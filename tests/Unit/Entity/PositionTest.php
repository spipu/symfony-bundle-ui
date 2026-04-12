<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

#[CoversNothing]
#[AllowMockObjectsWithoutExpectations]
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
