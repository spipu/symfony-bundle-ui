<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Event\GridDefinitionEvent;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\DataProviderInterface;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine;
use Spipu\UiBundle\Service\Ui\Grid\GridConfig;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Spipu\UiBundle\Service\Ui\GridManager;
use Spipu\UiBundle\Service\Ui\GridManagerInterface;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Tests\Unit\Service\Ui\Grid\GridConfigTest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class GridManagerTest extends AbstractTest
{
    /**
     * @param ContainerInterface $container
     * @return GridFactory
     */
    private function getGridFactory(ContainerInterface $container): GridFactory
    {
        $gridConfig = GridConfigTest::getService($this);

        return new GridFactory(
            $container,
            $container->get('request_stack'),
            $container->get('security.authorization_checker'),
            $container->get('router'),
            $container->get('event_dispatcher'),
            $container->get('twig'),
            $gridConfig
        );
    }

    public function testManager()
    {
        $dataProviderMock = SpipuUiMock::getDataProviderMock();
        $dataProviderMock->setNbEntities(100);

        $container = $this->getContainerMock(['data_provider' => $dataProviderMock]);
        $definition = SpipuUiMock::getGridDefinitionMock();

        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->anything(), GridDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode());

        $factory = $this->getGridFactory($container);

        $manager = $this->prepareManager($factory, $container, $definition, []);

        $this->assertInstanceOf(GridManagerInterface::class, $manager);
        $this->assertInstanceOf(DataProviderInterface::class, $manager->getDataProvider());
        $this->assertInstanceOf(GridRequest::class, $manager->getRequest());

        $twig = $container->get('twig');
        $twig
            ->expects($this->once())
            ->method('render')
            ->with($definition->getDefinition()->getTemplateAll(), ['manager' => $manager])
            ->willReturn('From template');

        $this->assertSame('From template', $manager->display());
        $this->assertSame(1, $manager->getNbPages());
        $this->assertSame(100, $manager->getNbTotalRows());
        $this->assertSame($manager->getDataProvider()->getPageRows(), $manager->getRows());
        $this->assertEmpty($manager->getInfoPages());
    }

    public function testManagerPager()
    {
        /** @var Request $request */

        // Prepare the grid definition
        $definition = SpipuUiMock::getGridDefinitionMock();
        $definition->getDefinition()->setPager(new Grid\Pager([20, 50, 100], 50));

        // Test : default value for current page and page length => Result(page=1 length=50)
        $manager = $this->prepareManagerReset($definition, []);
        $this->assertSame(1, $manager->getRequest()->getPageCurrent());
        $this->assertSame(1, $manager->getRequest()->getSessionValue('page_current', null));
        $this->assertSame(50, $manager->getRequest()->getPageLength());
        $this->assertSame(50, $manager->getRequest()->getSessionValue('page_length', null));
        $this->assertSame(20, $manager->getNbPages());
        $this->assertSame(999, $manager->getNbTotalRows());
        $this->assertSame(50, count($manager->getRows()));

        // Test : good page 1, good page length 20 => Result(page=1 length=20)
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 1, GridRequest::KEY_PAGE_LENGTH => 20]
        );
        $this->assertSame(1, $manager->getRequest()->getPageCurrent());
        $this->assertSame(1, $manager->getRequest()->getSessionValue('page_current', null));
        $this->assertSame(20, $manager->getRequest()->getPageLength());
        $this->assertSame(20, $manager->getRequest()->getSessionValue('page_length', null));
        $this->assertSame(50, $manager->getNbPages());
        $this->assertSame(999, $manager->getNbTotalRows());
        $this->assertSame(20, count($manager->getRows()));

        // Test : bad page -1, good page length 20 => Result(page=1 length=20)
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => -1, GridRequest::KEY_PAGE_LENGTH => 20]
        );
        $this->assertSame(1, $manager->getRequest()->getPageCurrent());
        $this->assertSame(1, $manager->getRequest()->getSessionValue('page_current', null));
        $this->assertSame(20, $manager->getRequest()->getPageLength());
        $this->assertSame(20, $manager->getRequest()->getSessionValue('page_length', null));
        $this->assertSame(50, $manager->getNbPages());
        $this->assertSame(999, $manager->getNbTotalRows());
        $this->assertSame(20, count($manager->getRows()));

        // Test : bad page 99, good page length 20 => Result(page=50 length=20)
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 99, GridRequest::KEY_PAGE_LENGTH => 20]
        );
        $this->assertSame(50, $manager->getRequest()->getPageCurrent());
        $this->assertSame(50, $manager->getRequest()->getSessionValue('page_current', null));
        $this->assertSame(20, $manager->getRequest()->getPageLength());
        $this->assertSame(20, $manager->getRequest()->getSessionValue('page_length', null));
        $this->assertSame(50, $manager->getNbPages());
        $this->assertSame(999, $manager->getNbTotalRows());
        $this->assertSame(19, count($manager->getRows()));

        // Test : good page 2, bad page length 25 => Result(page=2 length=50)
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 2, GridRequest::KEY_PAGE_LENGTH => 25]
        );
        $this->assertSame(2, $manager->getRequest()->getPageCurrent());
        $this->assertSame(2, $manager->getRequest()->getSessionValue('page_current', null));
        $this->assertSame(50, $manager->getRequest()->getPageLength());
        $this->assertSame(50, $manager->getRequest()->getSessionValue('page_length', null));
        $this->assertSame(20, $manager->getNbPages());
        $this->assertSame(999, $manager->getNbTotalRows());
        $this->assertSame(50, count($manager->getRows()));

        // Test : force good page value
        $manager->getRequest()->forcePageCurrent(3);
        $this->assertSame(3, $manager->getRequest()->getPageCurrent());
        $this->assertSame(3, $manager->getRequest()->getSessionValue('page_current', null));

        // Test : force bad page value
        $manager->getRequest()->forcePageCurrent(-1);
        $this->assertSame(1, $manager->getRequest()->getPageCurrent());
        $this->assertSame(1, $manager->getRequest()->getSessionValue('page_current', null));

        // Test : Page 1, length 50, 2 more pages => p 1 2 3 . 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 1, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => true],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => true,  'disabled' => false],
            ['name' => '2',   'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '3',   'url' => '/test/?t=1&pc=3&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=4&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 3, length 50, 2 more pages => p 1 2 3 4 5 . 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 3, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '2',   'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '3',   'url' => '/test/?t=1&pc=3&pl=50',  'active' => true,  'disabled' => false],
            ['name' => '4',   'url' => '/test/?t=1&pc=4&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '5',   'url' => '/test/?t=1&pc=5&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=6&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=4&pl=50',  'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 4, length 50, 2 more pages => p 1 2 3 4 5 6. 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 4, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=3&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '2',   'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '3',   'url' => '/test/?t=1&pc=3&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '4',   'url' => '/test/?t=1&pc=4&pl=50',  'active' => true,  'disabled' => false],
            ['name' => '5',   'url' => '/test/?t=1&pc=5&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '6',   'url' => '/test/?t=1&pc=6&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=7&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=5&pl=50',  'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 5, length 50, 2 more pages => p 1 . 3 4 5 6 7 . 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 5, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=4&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=2&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '3',   'url' => '/test/?t=1&pc=3&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '4',   'url' => '/test/?t=1&pc=4&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '5',   'url' => '/test/?t=1&pc=5&pl=50',  'active' => true,  'disabled' => false],
            ['name' => '6',   'url' => '/test/?t=1&pc=6&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '7',   'url' => '/test/?t=1&pc=7&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=8&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=6&pl=50',  'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 10, length 50, 2 more pages => p 1 . 8 9 10 11 12 . 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 10, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=9&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=7&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '8',   'url' => '/test/?t=1&pc=8&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '9',   'url' => '/test/?t=1&pc=9&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '10',  'url' => '/test/?t=1&pc=10&pl=50', 'active' => true,  'disabled' => false],
            ['name' => '11',  'url' => '/test/?t=1&pc=11&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '12',  'url' => '/test/?t=1&pc=12&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=13&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=11&pl=50', 'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 17, length 50, 2 more pages => p 1 . 15 16 17 18 19 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 17, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=16&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=14&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '15',  'url' => '/test/?t=1&pc=15&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '16',  'url' => '/test/?t=1&pc=16&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '17',  'url' => '/test/?t=1&pc=17&pl=50', 'active' => true,  'disabled' => false],
            ['name' => '18',  'url' => '/test/?t=1&pc=18&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '19',  'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=18&pl=50', 'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 17, length 50, 2 more pages => p 1 . 15 16 17 18 19 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 17, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=16&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=14&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '15',  'url' => '/test/?t=1&pc=15&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '16',  'url' => '/test/?t=1&pc=16&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '17',  'url' => '/test/?t=1&pc=17&pl=50', 'active' => true,  'disabled' => false],
            ['name' => '18',  'url' => '/test/?t=1&pc=18&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '19',  'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=18&pl=50', 'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 18, length 50, 2 more pages => p 1 . 16 17 18 19 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 18, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=17&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=15&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '16',  'url' => '/test/?t=1&pc=16&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '17',  'url' => '/test/?t=1&pc=17&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '18',  'url' => '/test/?t=1&pc=18&pl=50', 'active' => true,  'disabled' => false],
            ['name' => '19',  'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        // Test : Page 20, length 50, 2 more pages => p 1 . 18 19 20 n
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_PAGE_CURRENT => 20, GridRequest::KEY_PAGE_LENGTH => 50]
        );
        $expected = [
            ['name' => '«',   'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '1',   'url' => '/test/?t=1&pc=1&pl=50',  'active' => false, 'disabled' => false],
            ['name' => '...', 'url' => '/test/?t=1&pc=17&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '18',  'url' => '/test/?t=1&pc=18&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '19',  'url' => '/test/?t=1&pc=19&pl=50', 'active' => false, 'disabled' => false],
            ['name' => '20',  'url' => '/test/?t=1&pc=20&pl=50', 'active' => true,  'disabled' => false],
            ['name' => '»',   'url' => '/test/?t=1&pc=20&pl=50', 'active' => false, 'disabled' => true],
        ];
        $this->assertSame($expected, $manager->getInfoPages(2));

        $this->assertSame('/test/?t=1&pc=1&pl=50', $manager->getPageLengthUrl(50));
    }

    public function testManagerSort()
    {
        // Prepare the grid definition
        $definition = SpipuUiMock::getGridDefinitionMock();

        // Test : no default sort
        $manager = $this->prepareManagerReset($definition, []);
        $this->assertSame(null, $manager->getRequest()->getSortColumn());
        $this->assertSame(null, $manager->getRequest()->getSortOrder());

        // Test : default sort good with default value
        $definition->getDefinition()->setDefaultSort('field_a_a');
        $manager = $this->prepareManagerReset($definition, []);
        $this->assertSame('field_a_a', $manager->getRequest()->getSortColumn());
        $this->assertSame('asc', $manager->getRequest()->getSortOrder());
        $this->assertSame('field_a_a', $manager->getRequest()->getSessionValue('sort_column', null));
        $this->assertSame('asc', $manager->getRequest()->getSessionValue('sort_order', null));

        // Test : default sort good
        $definition->getDefinition()->setDefaultSort('field_a_b', 'desc');
        $manager = $this->prepareManagerReset($definition, []);
        $this->assertSame('field_a_b', $manager->getRequest()->getSortColumn());
        $this->assertSame('desc', $manager->getRequest()->getSortOrder());

        // Test : request sort good
        $definition->getDefinition()->setDefaultSort('field_a_a', 'asc');
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_SORT_COLUMN => 'field_a_b', GridRequest::KEY_SORT_ORDER => 'desc']
        );
        $this->assertSame('field_a_b', $manager->getRequest()->getSortColumn());
        $this->assertSame('desc', $manager->getRequest()->getSortOrder());

        // Test : request sort bad
        $definition->getDefinition()->setDefaultSort('field_a_a', 'asc');
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_SORT_COLUMN => 'field_a_c', GridRequest::KEY_SORT_ORDER => 'desc']
        );
        $this->assertSame(null, $manager->getRequest()->getSortColumn());
        $this->assertSame(null, $manager->getRequest()->getSortOrder());

        $this->assertSame('/test/?t=1&pc=1&pl=10000&sc=field_b_a&so=asc', $manager->getSortUrl('field_b_a', 'asc'));
    }

    public function testManagerFilter()
    {
        // Prepare the grid definition
        $definition = SpipuUiMock::getGridDefinitionMock();

        // Test : no filters
        $manager = $this->prepareManagerReset($definition, []);
        $this->assertSame([], $manager->getRequest()->getFilters());
        $this->assertSame([], $manager->getRequest()->getSessionValue('filters', null));

        // Test : bad column
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['test' => 'wrong', 'field_b_a' => ' good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());
        $this->assertSame('', $manager->getRequest()->getFilter('field_b_b'));
        $this->assertSame('good', $manager->getRequest()->getFilter('field_b_a'));
        $this->assertSame('', $manager->getRequest()->getFilter('field_b_a', 'from'));

        // Test : not filterable column
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_b_b' => 'wrong', 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : null value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_b' => null, 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : empty value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_b' => ' ', 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : good value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_b' => 1, 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_a_b' => '1', 'field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => 1, 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => [], 'field_b_a' => 'good']]
        );
        $this->assertSame(['field_b_a' => 'good'], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => ['bad' => 'value', 'from' => ' aaa']]]
        );
        $this->assertSame(['field_a_a' => ['from' => 'aaa']], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => ['bad' => 'value', 'to' => ' zzz']]]
        );
        $this->assertSame(['field_a_a' => ['to' => 'zzz']], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => ['from' => 'aaa', 'to' => 'zzz']]]
        );
        $this->assertSame(['field_a_a' => ['from' => 'aaa', 'to' => 'zzz']], $manager->getRequest()->getFilters());
        $this->assertSame('aaa', $manager->getRequest()->getFilter('field_a_a', 'from'));
        $this->assertSame('zzz', $manager->getRequest()->getFilter('field_a_a', 'to'));
        $this->assertSame('', $manager->getRequest()->getFilter('field_a_a', 'wrong'));

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => ['from' => ' ', 'to' => 'zzz']]]
        );
        $this->assertSame(['field_a_a' => ['to' => 'zzz']], $manager->getRequest()->getFilters());

        // Test : range value
        $manager = $this->prepareManagerReset(
            $definition,
            [GridRequest::KEY_FILTERS => ['field_a_a' => ['from' => 'aaa', 'to' => ' ']]]
        );
        $this->assertSame(['field_a_a' => ['from' => 'aaa']], $manager->getRequest()->getFilters());

        $expected = [
            'field_a_a' => $definition->getDefinition()->getColumn('field_a_a'),
            'field_a_b' => $definition->getDefinition()->getColumn('field_a_b'),
            'field_b_a' => $definition->getDefinition()->getColumn('field_b_a'),
        ];
        $this->assertSame($expected, $manager->getInfoFilters());
    }

    public function testManagerValues()
    {
        // Prepare the grid definition
        $definition = SpipuUiMock::getGridDefinitionMock();

        $manager = $this->prepareManagerReset($definition, []);

        $rows = $manager->getRows();
        /** @var EntityInterface $row */
        $row = array_shift($rows);

        $this->assertSame($row->getFieldAA(), $manager->getValue($row, 'fieldAA'));
        $this->assertSame($row->getFieldAA(), $manager->getValue($row, 'getFieldAA'));

        $this->expectException(GridException::class);
        $manager->getValue($row, 'wrongField');
    }

    public function testManagerGranted()
    {
        // Prepare the grid definition
        $definition = SpipuUiMock::getGridDefinitionMock();

        $manager = $this->prepareManagerReset($definition, []);
        /** @var EntityInterface $row */
        $row = $manager->getRows()[0];

        $action = new Grid\Action('enable', 'Enable', 30, 'enable');

        $this->assertTrue($manager->isGrantedAction($action, $row));

        $action->setNeededRole('ROLE_WRONG');
        $this->assertFalse($manager->isGrantedAction($action, $row));

        $action->setNeededRole('ROLE_GOOD');
        $this->assertTrue($manager->isGrantedAction($action, $row));

        $action->setNeededRole(null);

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => 2]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => 1]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['eq' => 2]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['eq' => 1]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['neq' => 1]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['neq' => 2]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['lt' => 1]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['lt' => 2]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['gt' => 1]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['gt' => 0]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['lte' => 0]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['lte' => 1]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['gte' => 2]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['gte' => 1]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['in' => [2,3,4]]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['in' => [1,2,3]]]), $row));

        $this->assertFalse($manager->isGrantedAction($action->setConditions(['fieldAA' => ['nin' => [1,2,3]]]), $row));
        $this->assertTrue($manager->isGrantedAction($action->setConditions(['fieldAA'  => ['nin' => [2,3,4]]]), $row));

        $this->assertTrue(
            $manager->isGrantedAction(
                $action->setConditions(['fieldAA' => ['gt' => 0, 'lt' => 2]]),
                $row
            )
        );
        $this->assertFalse(
            $manager->isGrantedAction(
                $action->setConditions(['fieldAA' => ['gt' => 0, 'lt' => 1]]),
                $row
            )
        );

        $this->assertTrue(
            $manager->isGrantedAction(
                $action->setConditions(['fieldAA' => ['eq' => 1], 'fieldAB' => ['eq' => 1]]),
                $row
            )
        );
        $this->assertFalse(
            $manager->isGrantedAction(
                $action->setConditions(['fieldAA' => ['eq' => 1], 'fieldAB' => ['eq' => 2]]),
                $row
            )
        );

        // Test callback
        $this->assertTrue(
            $manager->isGrantedAction(
                $action->setConditions(
                    ['fieldAA' => ['callback' => function ($row) { return $row->getFieldAA() === '1'; }]]
                ),
                $row
            )
        );
        $this->assertFalse(
            $manager->isGrantedAction(
                $action->setConditions(
                    ['fieldAA' => ['callback' => function ($row) { return $row->getFieldAA() === '2'; }]]
                ),
                $row
            )
        );

        $this->expectException(GridException::class);
        $manager->isGrantedAction($action->setConditions(['fieldAA'  => ['wrong' => 1]]), $row);
    }

    public function testManagerBadProvider()
    {
        $container = $this->getContainerMock(['data_provider' => new \stdClass()]);
        $definition = SpipuUiMock::getGridDefinitionMock();

        $factory = $this->getGridFactory($container);

        $this->expectException(GridException::class);
        $factory->create($definition);
    }

    public function testManagerMissingRoute()
    {
        $container = $this->getContainerMock(['data_provider' => SpipuUiMock::getDataProviderMock()]);

        $definition = SpipuUiMock::getGridDefinitionMock();

        $factory = $this->getGridFactory($container);
        $manager = $factory->create($definition);

        $this->expectException(GridException::class);
        $manager->validate();
    }

    public function testDataProviderDoctrine()
    {
        $definition = SpipuUiMock::getGridDefinitionMock();

        $manager = $this->prepareManagerReset($definition, []);

        $container = $this->getContainerMock();

        $dataProvider = new Doctrine($container->get('doctrine.orm.default_entity_manager'));
        $dataProvider->setGridRequest($manager->getRequest());
        $dataProvider->setGridDefinition($manager->getDefinition());
        $dataProvider->addCondition(['id' => 1]);

        $this->assertTrue(true);
    }

    /**
     * @param GridDefinitionInterface $definition
     * @param array $getValues
     * @return GridManager
     */
    private function prepareManagerReset(
        GridDefinitionInterface $definition,
        array $getValues = []
    ) {
        // Prepare the data provider
        $dataProviderMock = SpipuUiMock::getDataProviderMock();
        $dataProviderMock->setNbEntities(999);

        $container = $this->getContainerMock(['data_provider' => $dataProviderMock]);

        $factory = $this->getGridFactory($container);

        return $this->prepareManager($factory, $container, $definition, $getValues);
    }

    /**
     * @param GridFactory $factory
     * @param ContainerInterface $container
     * @param GridDefinitionInterface $definition
     * @param array $getValues
     * @return GridManager
     */
    private function prepareManager(
        GridFactory $factory,
        ContainerInterface $container,
        GridDefinitionInterface $definition,
        array $getValues = []
    ) {
        /** @var Request $request */
        $request = $container->get('request_stack')->getCurrentRequest();
        $request->initialize($getValues);

        $manager = $factory->create($definition);
        $manager->setRoute('test', ['t' => 1]);

        $this->assertFalse($manager->validate());

        return $manager;
    }
}
