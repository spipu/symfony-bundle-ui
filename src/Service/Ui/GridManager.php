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
use Spipu\UiBundle\Entity\GridConfig as GridConfigEntity;
use Spipu\UiBundle\Event\GridDefinitionEvent;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Grid\DataProvider\DataProviderInterface;
use Spipu\UiBundle\Service\Ui\Grid\GridConfig;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;
use Spipu\UiBundle\Entity\Grid\Action as GridAction;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Throwable;
use Twig\Environment as Twig;

/**
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 * @SuppressWarnings(PMD.CyclomaticComplexity)
 * @SuppressWarnings(PMD.TooManyFields)
 */
class GridManager implements GridManagerInterface
{
    private ContainerInterface $container;
    private GridRequest $request;
    private AuthorizationCheckerInterface $authorizationChecker;
    private RouterInterface $router;
    private EventDispatcherInterface $eventDispatcher;
    private Twig $twig;
    private GridConfig $gridConfig;
    private GridDefinition $definition;
    private DataProviderInterface $dataProvider;
    private ?GridConfigEntity $currentGridConfig;
    private ?string $routeName = null;
    private ?array $routeParameters = null;
    private ?array $gridConfigDefinition = null;
    private int $nbPages = 1;
    private int $nbTotalRows = 0;
    private array $rows = [];
    private bool $refreshNeeded = false;

    public function __construct(
        ContainerInterface $container,
        SymfonyRequest $symfonyRequest,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        Twig $twig,
        GridConfig $gridConfig,
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
        $this->gridConfig = $gridConfig;
    }

    private function initGridRequest(
        SymfonyRequest $symfonyRequest,
        RouterInterface $router
    ): GridRequest {
        return new GridRequest($symfonyRequest, $router, $this->definition);
    }

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

    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function setRoute(string $name, array $parameters = []): GridManagerInterface
    {
        $this->routeName = $name;
        $this->routeParameters = $parameters;

        return $this;
    }

    public function prepareRequest(): void
    {
        if (!$this->routeName) {
            throw new GridException('The Grid Manager is not ready');
        }

        $this->definition->prepareSort();

        $this->request->setRoute($this->routeName, $this->routeParameters);

        $this->prepareConfig();
        $this->loadCurrentGridConfig();

        $this->request->setCurrentConfig($this->currentGridConfig);
        $this->request->prepare();
    }

    private function prepareConfig(): void
    {
        if (!$this->definition->isPersonalize()) {
            return;
        }

        $configParams = $this->request->getConfigParams();
        if ($configParams === null) {
            return;
        }

        $configAction = (string) $configParams['action'];
        unset($configParams['action']);

        try {
            $gridConfig = $this->gridConfig->makeAction($this->getDefinition(), $configAction, $configParams);
            if ($gridConfig !== null) {
                $this->request->updateCurrentConfigId($gridConfig->getId());
            }
        } catch (Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $this->refreshNeeded = true;
    }

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

    public function validate(): bool
    {
        $this->prepareRequest();
        $this->loadPage();

        return $this->refreshNeeded;
    }

    public function getNbTotalRows(): int
    {
        return $this->nbTotalRows;
    }

    public function getNbPages(): int
    {
        return $this->nbPages;
    }

    public function getRequest(): GridRequest
    {
        return $this->request;
    }

    public function display(): string
    {
        if ($this->refreshNeeded) {
            throw new GridException(
                'This grid need a refresh, but the controller does not manage it after validation'
            );
        }

        return $this->twig->render(
            $this->getDefinition()->getTemplateAll(),
            [
                'manager' => $this,
            ]
        );
    }

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

    public function getCurrentUrl(array $params = []): string
    {
        return $this->request->getCurrentUrl($params);
    }

    public function getCurrentResetUrl(array $params = []): string
    {
        return $this->request->getCurrentResetUrl($params);
    }

    public function getSortUrl(string $column, string $order): string
    {
        return $this->getCurrentUrl(
            [
                GridRequest::KEY_SORT_COLUMN => $column,
                GridRequest::KEY_SORT_ORDER => $order,
            ]
        );
    }

    public function getPageLengthUrl(int $pageLength): string
    {
        return $this->getCurrentUrl(
            [
                GridRequest::KEY_PAGE_CURRENT => 1,
                GridRequest::KEY_PAGE_LENGTH => $pageLength,
            ]
        );
    }

    public function getActionLimit(): int
    {
        return $this->definition->getActionLimit();
    }

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
                    $granted = $granted && in_array($value, $condValue, true);
                    break;

                case 'nin':
                    $granted = $granted && !in_array($value, $condValue, true);
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

    public function getValue(EntityInterface $object, string $field): mixed
    {
        $finalObject = $object;
        $finalField = $field;

        if (str_contains($field, '.')) {
            [$subMethod, $finalField] = explode('.', $field, 2);
            $subMethod = 'get' . ucfirst($subMethod);

            if (!method_exists($object, $subMethod)) {
                throw new GridException(
                    'Unable to find field ' . $field . ' on object ' . get_class($object)
                );
            }

            $finalObject = $object->{$subMethod}();

            if ($finalObject === null) {
                return null;
            }
        }

        $methods = [
            'get' . ucfirst($finalField),
            'is' . ucfirst($finalField),
            $finalField,
        ];

        foreach ($methods as $method) {
            if (method_exists($finalObject, $method)) {
                return $finalObject->{$method}();
            }
        }

        throw new GridException(
            'Unable to find field ' . $field . ' on object ' . get_class($object)
        );
    }

    /**
     * Get the list of the columns to filter
     * @return Column[]
     */
    public function getInfoFilters(): array
    {
        return $this->definition->getFilterableColumns();
    }

    /**
     * Get the list of the columns to filter
     * @return Column[]
     */
    public function getInfoQuickSearch(): array
    {
        return $this->definition->getQuickSearchColumns();
    }

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

        $pages = [];
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
        return [
            'name'     => ($forceName !== '') ? $forceName : (string) $page,
            'url'      => $this->getCurrentUrl([GridRequest::KEY_PAGE_CURRENT => $page]),
            'active'   => (!$disabled && ($page === $this->request->getPageCurrent())),
            'disabled' => $disabled
        ];
    }

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

    public function getPersonalizeDefinition(): array
    {
        return $this->gridConfigDefinition;
    }

    public function getCurrentGridConfig(): ?GridConfigEntity
    {
        return $this->currentGridConfig;
    }

    private function loadCurrentGridConfig(): void
    {
        $this->currentGridConfig = null;
        $this->gridConfigDefinition = [];

        if (!$this->definition->isPersonalize()) {
            return;
        }

        $currentConfigId = $this->request->getGridConfigId();

        $this->gridConfigDefinition = $this->gridConfig->getPersonalizeDefinition(
            $this->getDefinition(),
            $currentConfigId
        );

        $currentConfigId = $this->gridConfigDefinition['current'];
        if ($currentConfigId !== null) {
            $this->currentGridConfig = $this->gridConfigDefinition['configs'][$currentConfigId];
        }
    }

    private function addFlash(string $type, string $message): void
    {
        $this->request->getSymfonyRequest()->getSession()->getFlashBag()->add($type, $message);
    }
}
