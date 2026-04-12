<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity;

use DateTimeInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\TimestampableTrait;

#[CoversNothing]
#[AllowMockObjectsWithoutExpectations]
class TimestampableTest extends TestCase
{
    public function testEntity(): void
    {
        $entity = new TimestampableEntity();

        $entity->setCreatedAtValue();
        $entity->setUpdatedAtValue();

        $this->assertInstanceOf(DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertInstanceOf(DateTimeInterface::class, $entity->getUpdatedAt());
    }
}

class TimestampableEntity
{
    use TimestampableTrait;
}
