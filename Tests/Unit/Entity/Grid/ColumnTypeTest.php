<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Form\Options\YesNo;

class ColumnTypeTest extends TestCase
{
    public function testEntity()
    {
        $this->goodEntityType(Grid\ColumnType::TYPE_INTEGER);
        $this->goodEntityType(Grid\ColumnType::TYPE_DATE);
        $this->goodEntityType(Grid\ColumnType::TYPE_DATETIME);
        $this->goodEntityType(Grid\ColumnType::TYPE_FLOAT);
        $this->goodEntityType(Grid\ColumnType::TYPE_SELECT, true);
        $entity = $this->goodEntityType(Grid\ColumnType::TYPE_TEXT);

        $entity->setTranslate(true);
        $this->assertSame(true, $entity->isTranslate());

        $entity->setTranslate(false);
        $this->assertSame(false, $entity->isTranslate());

        $entity->setTemplateField('test.html.twig');
        $this->assertSame('test.html.twig', $entity->getTemplateField());

        $options = new YesNo();
        $entity->setOptions($options);
        $this->assertSame($options, $entity->getOptions());
    }

    private function goodEntityType(string $type, bool $translate = false): Grid\ColumnType
    {
        $entity = new Grid\ColumnType($type);
        $this->assertSame($type, $entity->getType());
        $this->assertSame('@SpipuUi/grid/field/'.$type.'.html.twig', $entity->getTemplateField());
        $this->assertSame($translate, $entity->isTranslate());
        $this->assertSame(null, $entity->getOptions());

        return $entity;
    }

    public function testBadType()
    {
        $this->expectException(GridException::class);
        $entity = new Grid\ColumnType('bad-type');
    }
}
