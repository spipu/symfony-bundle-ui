<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Exception\GridException;

class GridTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Grid\Grid('grid_code', 'grid_entity');
        $this->assertSame('grid_code', $entity->getCode());
        $this->assertSame('grid_entity', $entity->getEntityName());

        $entity->setEntityName('test_entity');
        $this->assertSame('test_entity', $entity->getEntityName());

        $entity->setPrimaryKey();
        $this->assertSame('id', $entity->getDataProviderPrimaryKey());
        $this->assertSame('id', $entity->getRequestPrimaryKey());

        $entity->setPrimaryKey('data_id', 'request_id');
        $this->assertSame('data_id', $entity->getDataProviderPrimaryKey());
        $this->assertSame('request_id', $entity->getRequestPrimaryKey());

        $this->assertSame(
            'Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine',
            $entity->getDataProviderServiceName()
        );
        $entity->setDataProviderServiceName('dataProvider');
        $this->assertSame(
            'dataProvider',
            $entity->getDataProviderServiceName()
        );
    }

    public function testEntityTemplates()
    {
        $entity = new Grid\Grid('grid_code');

        $this->assertSame('@SpipuUi/grid/all.html.twig', $entity->getTemplateAll());
        $this->assertSame('@SpipuUi/grid/header.html.twig', $entity->getTemplateHeader());
        $this->assertSame('@SpipuUi/grid/filters.html.twig', $entity->getTemplateFilters());
        $this->assertSame('@SpipuUi/grid/pager.html.twig', $entity->getTemplatePager());
        $this->assertSame('@SpipuUi/grid/page.html.twig', $entity->getTemplatePage());
        $this->assertSame('@SpipuUi/grid/row.html.twig', $entity->getTemplateRow());
        $this->assertSame('@SpipuUi/grid/actions.html.twig', $entity->getTemplateActions());

        $entity
            ->setTemplateAll('test_all.html.twig')
            ->setTemplateHeader('test_header.html.twig')
            ->setTemplateFilters('test_filters.html.twig')
            ->setTemplatePager('test_pager.html.twig')
            ->setTemplatePage('test_page.html.twig')
            ->setTemplateRow('test_row.html.twig')
            ->setTemplateActions('test_actions.html.twig');

        $this->assertSame('test_all.html.twig', $entity->getTemplateAll());
        $this->assertSame('test_header.html.twig', $entity->getTemplateHeader());
        $this->assertSame('test_filters.html.twig', $entity->getTemplateFilters());
        $this->assertSame('test_pager.html.twig', $entity->getTemplatePager());
        $this->assertSame('test_page.html.twig', $entity->getTemplatePage());
        $this->assertSame('test_row.html.twig', $entity->getTemplateRow());
        $this->assertSame('test_actions.html.twig', $entity->getTemplateActions());
    }

    public function testEntityPager()
    {
        $entity = new Grid\Grid('grid_code');

        $pager = new Grid\Pager();
        $entity->setPager($pager);
        $this->assertSame($pager, $entity->getPager());
    }

    public function testEntityOptions()
    {
        $entity = new Grid\Grid('grid_code');

        $entity->addOption('a', 1);
        $this->assertSame(['a' => 1], $entity->getOptions());
    }

    public function testEntitySortOk()
    {
        $entity = new Grid\Grid('grid_code');
        $entity->addColumn((new Grid\Column('id', 'id', 'id', 10)));

        $this->assertSame(null, $entity->getDefaultSortColumn());
        $this->assertSame(null, $entity->getDefaultSortOrder());

        $entity->setDefaultSort('id');
        $this->assertSame('id', $entity->getDefaultSortColumn());
        $this->assertSame('asc', $entity->getDefaultSortOrder());

        $entity->setDefaultSort('id', 'desc');
        $this->assertSame('id', $entity->getDefaultSortColumn());
        $this->assertSame('desc', $entity->getDefaultSortOrder());

        $entity->setDefaultSort('id', 'asc');
        $this->assertSame('id', $entity->getDefaultSortColumn());
        $this->assertSame('asc', $entity->getDefaultSortOrder());
    }

    public function testEntitySortBadOrder()
    {
        $entity = new Grid\Grid('grid_code');
        $entity->addColumn((new Grid\Column('id', 'id', 'id', 10)));

        $this->expectException(GridException::class);
        $entity->setDefaultSort('id', 'toto');
    }

    public function testEntitySortBadColumn()
    {
        $entity = new Grid\Grid('grid_code');
        $entity->addColumn((new Grid\Column('id', 'id', 'id', 10)));

        $this->expectException(GridException::class);
        $entity->setDefaultSort('wrong');
    }

    public function testEntityColumns()
    {
        $entity = new Grid\Grid('grid_code');

        $columnA = new Grid\Column('code_a', 'name_a', 'field_a', 10);
        $columnB = new Grid\Column('code_b', 'name_b', 'field_b', 30);
        $columnC = new Grid\Column('code_c', 'name_c', 'field_c', 20);
        $columnD = new Grid\Column('code_d', 'name_d', 'field_d', 40);

        $columnC->setDisplayed(false);

        $entity->addColumn($columnA);
        $entity->addColumn($columnB);
        $entity->addColumn($columnC);
        $entity->addColumn($columnD);

        $this->assertSame($columnA, $entity->getColumn($columnA->getCode()));
        $this->assertSame($columnB, $entity->getColumn($columnB->getCode()));
        $this->assertSame($columnC, $entity->getColumn($columnC->getCode()));
        $this->assertSame($columnD, $entity->getColumn($columnD->getCode()));
        $this->assertSame(null, $entity->getColumn('code_wrong'));
        $this->assertSame(
            [
                $columnA->getCode() => $columnA,
                $columnB->getCode() => $columnB,
                $columnC->getCode() => $columnC,
                $columnD->getCode() => $columnD,
            ],
            $entity->getColumns()
        );

        $this->assertSame(
            [
                $columnA->getCode() => $columnA,
                $columnB->getCode() => $columnB,
                $columnD->getCode() => $columnD,
            ],
            $entity->getDisplayedColumns()
        );

        $entity->removeColumn('code_wrong');
        $entity->removeColumn($columnD->getCode());
        $this->assertSame(null, $entity->getColumn($columnD->getCode()));
        $this->assertSame(
            [
                $columnA->getCode() => $columnA,
                $columnB->getCode() => $columnB,
                $columnC->getCode() => $columnC,
            ],
            $entity->getColumns()
        );

        $entity->prepareSort();

        $this->assertSame(
            [
                $columnA->getCode() => $columnA,
                $columnC->getCode() => $columnC,
                $columnB->getCode() => $columnB,
            ],
            $entity->getColumns()
        );
    }

    public function testEntityRowActions()
    {
        $this->goodEntityAction('addRowAction', 'removeRowAction', 'getRowActions', 'getRowAction');
    }

    public function testEntityMassAction()
    {
        $this->goodEntityAction('addMassAction', 'removeMassAction', 'getMassActions', 'getMassAction');
    }

    public function testEntityGlobalAction()
    {
        $this->goodEntityAction('addGlobalAction', 'removeGlobalAction', 'getGlobalActions', 'getGlobalAction');
    }

    private function goodEntityAction(string $methodAdd, string $methodRemove, string $methodGetAll, string $methodGet)
    {
        $entity = new Grid\Grid('grid_code');

        $actionA = new Grid\Action('code_a', 'name_a', 10, 'route_a');
        $actionB = new Grid\Action('code_b', 'name_b', 30, 'route_b');
        $actionC = new Grid\Action('code_c', 'name_c', 20, 'route_c');
        $actionD = new Grid\Action('code_d', 'name_d', 40, 'route_d');

        $entity->{$methodAdd}($actionA);
        $entity->{$methodAdd}($actionB);
        $entity->{$methodAdd}($actionC);
        $entity->{$methodAdd}($actionD);

        $this->assertSame(
            [
                $actionA->getCode() => $actionA,
                $actionB->getCode() => $actionB,
                $actionC->getCode() => $actionC,
                $actionD->getCode() => $actionD,
            ],
            $entity->{$methodGetAll}()
        );

        $this->assertSame($actionA, $entity->{$methodGet}($actionA->getCode()));
        $this->assertSame($actionB, $entity->{$methodGet}($actionB->getCode()));
        $this->assertSame($actionC, $entity->{$methodGet}($actionC->getCode()));
        $this->assertSame($actionD, $entity->{$methodGet}($actionD->getCode()));

        $entity->{$methodRemove}('code_wrong');
        $entity->{$methodRemove}($actionD->getCode());
        $this->assertSame(
            [
                $actionA->getCode() => $actionA,
                $actionB->getCode() => $actionB,
                $actionC->getCode() => $actionC,
            ],
            $entity->{$methodGetAll}()
        );

        $this->assertSame(null, $entity->{$methodGet}($actionD->getCode()));

        $entity->prepareSort();

        $this->assertSame(
            [
                $actionA->getCode() => $actionA,
                $actionC->getCode() => $actionC,
                $actionB->getCode() => $actionB,
            ],
            $entity->{$methodGetAll}()
        );
    }
}
