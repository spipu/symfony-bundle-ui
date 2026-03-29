<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\TimestampableTrait;

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
