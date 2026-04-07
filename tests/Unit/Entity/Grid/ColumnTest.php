<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(Grid\Column::class)]
class ColumnTest extends TestCase
{
    public function testEntity(): void
    {
        $entity = new Grid\Column('code', 'name_1', 'id_1', 10);

        $this->assertSame('code', $entity->getCode());
        $this->assertSame('name_1', $entity->getName());
        $this->assertSame('id_1', $entity->getEntityField());
        $this->assertSame(10, $entity->getPosition());
        $this->assertSame([], $entity->getOptions());
        $this->assertSame(false, $entity->getFilter()->isFilterable());
        $this->assertSame('text', $entity->getType()->getType());
        $this->assertSame(false, $entity->isSortable());
        $this->assertSame(true, $entity->isDisplayed());

        $entity
            ->setName('name_2')
            ->setEntityField('id_2')
            ->useSortable(true)
            ->setFilter(new Grid\ColumnFilter(true))
            ->setType(new Grid\ColumnType('integer'));

        $this->assertSame('name_2', $entity->getName());
        $this->assertSame('id_2', $entity->getEntityField());
        $this->assertSame(true, $entity->isSortable());
        $this->assertSame(true, $entity->isDisplayed());
        $this->assertSame(true, $entity->getFilter()->isFilterable());
        $this->assertSame('integer', $entity->getType()->getType());

        $entity->addOption('a', 1);
        $this->assertSame(['a' => 1], $entity->getOptions());

        $entity->setDisplayed(true);
        $this->assertSame(true, $entity->isSortable());
        $this->assertSame(true, $entity->isDisplayed());

        $entity->setDisplayed(false);
        $this->assertSame(true, $entity->isSortable());
        $this->assertSame(false, $entity->isDisplayed());

        $entity->setDisplayed(true);
        $this->assertSame(true, $entity->isSortable());
        $this->assertSame(true, $entity->isDisplayed());
    }
}
