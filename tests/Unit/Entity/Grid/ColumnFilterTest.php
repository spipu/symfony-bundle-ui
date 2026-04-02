<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;

class ColumnFilterTest extends TestCase
{
    public function testEntity(): void
    {
        $entity = new Grid\ColumnFilter(true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(true, true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(true, false);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(false, true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity->useQuickSearch(false);
        $entity->useFilterable(true);
        $this->assertSame(true, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity->useFilterable(false);
        $entity->useQuickSearch(true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity->useRange(true);
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(true, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter(false);
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity->useExactValue(true);
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(true, $entity->isExactValue());
        $this->assertSame(false, $entity->isMultipleValues());

        $entity = new Grid\ColumnFilter();
        $entity->useMultipleValues(true);
        $this->assertSame(false, $entity->isQuickSearch());
        $this->assertSame(false, $entity->isFilterable());
        $this->assertSame(false, $entity->isRange());
        $this->assertSame(false, $entity->isExactValue());
        $this->assertSame(true, $entity->isMultipleValues());

        $type = new Grid\ColumnType('select');

        $entity = new Grid\ColumnFilter(true);
        $entity->linkToColumnType($type);
        $this->assertSame('@SpipuUi/grid/filter/select.html.twig', $entity->getTemplateFilter());

        $entity = new Grid\ColumnFilter(true);
        $entity->setTemplateFilter('test.html.twig');
        $this->assertSame('test.html.twig', $entity->getTemplateFilter());
        $entity->linkToColumnType($type);
        $this->assertSame('test.html.twig', $entity->getTemplateFilter());

        // ValueTransformer - default state
        $entity = new Grid\ColumnFilter(true);
        $this->assertNull($entity->getValueTransformer());

        // ValueTransformer - with transformer
        $result = $entity->setValueTransformer(fn(string $v): string => strtoupper($v));
        $this->assertSame($entity, $result);
        $this->assertNotNull($entity->getValueTransformer());
    }
}
