<?php
namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\TimestampableTrait;

class TimestampableTest extends TestCase
{
    public function testEntity()
    {
        $entity = new TimestampableEntity();

        $this->assertSame($entity, $entity->setCreatedAtValue());
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreatedAt());

        $this->assertSame($entity, $entity->setUpdatedAtValue());
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getUpdatedAt());
    }
}

class TimestampableEntity
{
    use TimestampableTrait;
}
