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
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity = new Grid\ColumnFilter(true, true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity = new Grid\ColumnFilter(true, false);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity = new Grid\ColumnFilter(false, true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity->useQuickSearch(false);
        $entity->useFilterable(true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity->useFilterable(false);
        $entity->useQuickSearch(true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isRange());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity->useRange(true);
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());

        $entity->useExactValue(true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(true, $entity->isExactValue());

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
