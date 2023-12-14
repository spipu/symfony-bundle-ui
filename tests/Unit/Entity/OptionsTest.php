<?php
namespace Spipu\UiBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\OptionsTrait;

class OptionsTest extends TestCase
{
    public function testEntity()
    {
        $entity = new OptionsEntity();
        $this->assertSame([], $entity->getOptions());

        $entity->addOption('a', 1);
        $this->assertSame(['a' => 1], $entity->getOptions());

        $entity->addOption('b', 2);
        $this->assertSame(['a' => 1, 'b' => 2], $entity->getOptions());

        $this->assertSame($entity, $entity->setOptions(['c' => 3]));
        $this->assertSame(['c' => 3], $entity->getOptions());

        $entity->addOption('d', 4);
        $this->assertSame(['c' => 3, 'd' => 4], $entity->getOptions());

        $this->assertSame(null, $entity->getOption('a'));
        $this->assertSame(false, $entity->getOption('a', false));
        $this->assertSame(3, $entity->getOption('c'));
    }
}

class OptionsEntity
{
    use OptionsTrait;
}
