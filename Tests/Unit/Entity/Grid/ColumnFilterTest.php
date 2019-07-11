<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;

class ColumnFilterTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Grid\ColumnFilter(true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());

        $entity->useFilterable(true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());

        $entity->useRange(true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isRange());


        $type = new Grid\ColumnType('select');

        $entity = new Grid\ColumnFilter(true);
        $entity->linkToColumnType($type);
        $this->assertSame('@SpipuUi/grid/filter/select.html.twig', $entity->getTemplateFilter());

        $entity = new Grid\ColumnFilter(true);
        $entity->setTemplateFilter('test.html.twig');
        $this->assertSame('test.html.twig', $entity->getTemplateFilter());
        $entity->linkToColumnType($type);
        $this->assertSame('test.html.twig', $entity->getTemplateFilter());
    }
}
