<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UiBundle\Service\Ui;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\Action;
use Spipu\UiBundle\Entity\Grid\Column;
use Spipu\UiBundle\Event\GridDefinitionEvent;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\DataProviderInterface;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;
use Spipu\UiBundle\Entity\Grid\Action as GridAction;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment as Twig;
use Twig\Error\Error as TwigError;

/**
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 * @SuppressWarnings(PMD.CyclomaticComplexity)
 */
class GridManager implements GridManagerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var GridRequest
     */
    private $request;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var GridDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @var int
     */
    private $nbPages = 1;

    /**
     * @var int
     */
    private $nbTotalRows = 0;

    /**
     * @var object[]
     */
    private $rows;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * GridManager constructor.
     * @param ContainerInterface $container
     * @param SymfonyRequest $symfonyRequest
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param Twig $twig
     * @param GridDefinitionInterface $gridDefinition
     * @throws GridException
     */
    public function __construct(
        ContainerInterface $container,
        SymfonyRequest $symfonyRequest,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        Twig $twig,
        GridDefinitionInterface $gridDefinition
    ) {
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->twig = $twig;
        $this->definition = $gridDefinition->getDefinition();

        $event = new GridDefinitionEvent($this->definition);
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $this->request = $this->initGridRequest($symfonyRequest, $router);
        $this->dataProvider = $this->initDataProvider();
    }

    /**
     * @param SymfonyRequest $symfonyRequest
     * @param RouterInterface $router
     * @return GridRequest
     */
    private function initGridRequest(
        SymfonyRequest $symfonyRequest,
        RouterInterface $router
    ): GridRequest {
        return new GridRequest($symfonyRequest, $router, $this->definition);
    }

    /**
     * @return DataProviderInterface
     * @throws GridException
     */
    private function initDataProvider(): DataProviderInterface
    {
        $dataProvider = clone $this->container->get($this->definition->getDataProviderServiceName());
        if (!($dataProvider instanceof DataProviderInterface)) {
            throw new GridException('The Data Provider must implement DataProviderInterface');
        }

        $dataProvider->setGridRequest($this->request);
        $dataProvider->setGridDefinition($this->definition);

        return $dataProvider;
    }

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return GridManagerInterface
     */
    public function setRoute(string $name, array $parameters = []): GridManagerInterface
    {
        $this->routeName = $name;
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * @return void
     * @throws GridException
     */
    public function prepareRequest(): void
    {
        if (!$this->routeName) {
            throw new GridException('The Grid Manager is not ready');
        }

        $this->definition->prepareSort();

        $this->request->prepare($this->routeName, $this->routeParameters);
    }

    /**
     * @return void
     */
    public function loadPage(): void
    {
        $this->nbPages = 1;
        $this->nbTotalRows = $this->dataProvider->getNbTotalRows();

        if ($this->definition->getPager()) {
            $this->nbPages = (int) floor(($this->nbTotalRows - 1) / $this->request->getPageLength()) + 1;
            if ($this->request->getPageCurrent() > $this->nbPages) {
                $this->request->forcePageCurrent($this->nbPages);
            }
        }

        $this->rows = $this->dataProvider->getPageRows();
    }

    /**
     * @return bool
     * @throws GridException
     */
    public function validate(): bool
    {
        $this->prepareRequest();
        $this->loadPage();

        return true;
    }

    /**
     * @return int
     */
    public function getNbTotalRows(): int
    {
        return $this->nbTotalRows;
    }

    /**
     * @return int
     */
    public function getNbPages(): int
    {
        return $this->nbPages;
    }

    /**
     * @return GridRequest
     */
    public function getRequest(): GridRequest
    {
        return $this->request;
    }

    /**
     * @return string
     * @throws TwigError
     */
    public function display(): string
    {
        return $this->twig->render(
            $this->getDefinition()->getTemplateAll(),
            [
                'manager' => $this,
            ]
        );
    }

    /**
     * @return GridDefinition
     */
    public function getDefinition(): GridDefinition
    {
        return $this->definition;
    }

    /**
     * @return object[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getCurrentUrl(array $params = []): string
    {
        return $this->request->getCurrentUrl($params);
    }

    /**
     * @param string $column
     * @param string $order
     * @return string
     */
    public function getSortUrl(string $column, string $order): string
    {
        return $this->getCurrentUrl(
            [
                GridRequest::KEY_SORT_COLUMN => $column,
                GridRequest::KEY_SORT_ORDER => $order,
            ]
        );
    }

    /**
     * @param int $pageLength
     * @return string
     */
    public function getPageLengthUrl(int $pageLength): string
    {
        return $this->getCurrentUrl(
            [
                GridRequest::KEY_PAGE_CURRENT => 1,
                GridRequest::KEY_PAGE_LENGTH => $pageLength,
            ]
        );
    }

    /**
     * @param GridAction $action
     * @param EntityInterface|null $object
     * @return bool
     * @throws GridException
     */
    public function isGrantedAction(GridAction $action, EntityInterface $object = null): bool
    {
        if ($action->getNeededRole() && !$this->authorizationChecker->isGranted($action->getNeededRole())) {
            return false;
        }

        if (empty($action->getConditions())) {
            return true;
        }

        foreach ($action->getConditions() as $field => $condition) {
            if (!is_array($condition)) {
                $condition = ['eq' => $condition];
            }
            if (!$this->isGrantedCondition($object, $field, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param EntityInterface $object
     * @param string $field
     * @param array $condition
     * @return bool
     * @throws GridException
     */
    private function isGrantedCondition(EntityInterface $object, string $field, array $condition): bool
    {
        $value = $this->getValue($object, $field);

        $granted = true;
        foreach ($condition as $condType => $condValue) {
            switch ($condType) {
                case 'eq':
                    $granted = $granted && ($value == $condValue);
                    break;

                case 'neq':
                    $granted = $granted && ($value != $condValue);
                    break;

                case 'lt':
                    $granted = $granted && ($value < $condValue);
                    break;

                case 'gt':
                    $granted = $granted && ($value > $condValue);
                    break;

                case 'lte':
                    $granted = $granted && ($value <= $condValue);
                    break;

                case 'gte':
                    $granted = $granted && ($value >= $condValue);
                    break;

                case 'in':
                    $granted = $granted && in_array($value, $condValue);
                    break;

                case 'nin':
                    $granted = $granted && !in_array($value, $condValue);
                    break;

                case 'callback':
                    $granted = $granted && call_user_func_array($condValue, [$object]);
                    break;

                default:
                    throw new GridException('Unknown Action Condition Type');
            }
        }

        return $granted;
    }

    /**
     * @param EntityInterface $object
     * @param string $field
     * @return mixed
     * @throws GridException
     */
    public function getValue(EntityInterface $object, string $field)
    {
        $methods = [
            'get' . ucfirst($field),
            'is' . ucfirst($field),
            $field,
        ];

        foreach ($methods as $method) {
            if (method_exists($object, $method)) {
                return $object->{$method}();
            }
        }

        throw new GridException('Unable to find the field ' . $field . ' on the object ' . get_class($object));
    }

    /**
     * Get the list of the columns to filter
     * @return Column[]
     */
    public function getInfoFilters(): array
    {
        $columns = [];
        foreach ($this->definition->getColumns() as $column) {
            if ($column->getFilter()->isFilterable()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * Get the list of the columns to filter
     * @return Column[]
     */
    public function getInfoQuickSearch(): array
    {
        $columns = [];
        foreach ($this->definition->getColumns() as $column) {
            if ($column->getFilter()->isQuickSearch()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param int $maxPages
     * @return array
     */
    public function getInfoPages(int $maxPages = 4): array
    {
        if ($this->definition->getPager() === null) {
            return [];
        }

        $pageMin = 1;
        $pageMax = $this->nbPages;
        $pageCur = $this->request->getPageCurrent();
        $pagePrevious = ($this->request->getPageCurrent() > 1 ? $this->request->getPageCurrent() - 1 : 1);
        $pageNext     = ($this->request->getPageCurrent() < $pageMax ? $this->request->getPageCurrent() + 1 : $pageMax);

        $pages = array();
        $pages[] = $this->getInfoPage($pagePrevious, '«', $pageMin == $pageCur);
        $pages[] = $this->getInfoPage($pageMin);

        if ($pageMin + $maxPages + 1 < $pageCur) {
            $pages[] = $this->getInfoPage($pageCur - $maxPages - 1, '...');
        }

        $start = max($pageMin + 1, $pageCur - $maxPages);
        $end = min($pageMax - 1, $pageCur + $maxPages);
        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $this->getInfoPage($page);
        }

        if ($pageMax - $maxPages - 1 > $pageCur) {
            $pages[] = $this->getInfoPage($pageCur + $maxPages + 1, '...');
        }
        if ($pageMax > 1) {
            $pages[] = $this->getInfoPage($pageMax);
        }
        $pages[] = $this->getInfoPage($pageNext, '»', $pageMax == $pageCur);

        return $pages;
    }

    /**
     * Prepare the page
     *
     * @param int $page
     * @param string $forceName
     * @param bool $disabled
     * @return array
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    private function getInfoPage(int $page, string $forceName = '', bool $disabled = false): array
    {
        return array(
            'name'     => ($forceName !== '') ? $forceName : (string) $page,
            'url'      => $this->getCurrentUrl([GridRequest::KEY_PAGE_CURRENT => $page]),
            'active'   => (!$disabled && ($page === $this->request->getPageCurrent())),
            'disabled' => $disabled
        );
    }

    /**
     * @param GridAction $action
     * @param array $actionParams
     * @param EntityInterface|null $row
     * @return string
     */
    public function buildActionUrl(Action $action, array $actionParams, ?EntityInterface $row): string
    {
        if ($action->getBuildCallback()) {
            return call_user_func_array(
                $action->getBuildCallback(),
                [
                    $this->router,
                    $action,
                    $actionParams,
                    $row
                ]
            );
        }

        return $this->router->generate($action->getRouteName(), array_merge($action->getRouteParams(), $actionParams));
    }
}
