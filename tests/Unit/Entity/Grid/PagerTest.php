<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;

class PagerTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Grid\Pager();
        $this->assertSame([10, 20, 50, 100], $entity->getLengths());
        $this->assertSame(20, $entity->getDefaultLength());

        $entity = new Grid\Pager([50, 100, 500], 100);
        $this->assertSame([50, 100, 500], $entity->getLengths());
        $this->assertSame(100, $entity->getDefaultLength());

        $entity->setLengths([]);
        $this->assertSame([20], $entity->getLengths());
        $this->assertSame(20, $entity->getDefaultLength());

        $entity->setLengths([-5, 1, 10, 5 => '20', 'c' => 50]);
        $this->assertSame([1, 10, 20, 50], $entity->getLengths());
        $this->assertSame(1, $entity->getDefaultLength());

        $entity->setDefaultLength(10);
        $this->assertSame(10, $entity->getDefaultLength());

        $entity->setDefaultLength(15);
        $this->assertSame(1, $entity->getDefaultLength());
    }
}
