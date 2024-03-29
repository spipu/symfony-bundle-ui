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

namespace Spipu\UiBundle\Service\Ui\Grid;

use Spipu\UiBundle\Entity\GridConfig as GridConfigEntity;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PMD.CyclomaticComplexity)
 * @SuppressWarnings(PMD.NPathComplexity)
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 */
class GridRequest
{
    public const MAX_AUTHORIZED_PAGE_LENGTH = 10000;

    public const KEY_PAGE_LENGTH  = 'pl';
    public const KEY_PAGE_CURRENT = 'pc';
    public const KEY_SORT_COLUMN  = 'sc';
    public const KEY_SORT_ORDER   = 'so';
    public const KEY_CONFIG       = 'cf';
    public const KEY_FILTERS      = 'fl';
    public const KEY_QUICK_SEARCH = 'qs';

    private SymfonyRequest $request;
    private RouterInterface $router;
    private GridDefinition $definition;
    private string $sessionPrefixGridKey = '';
    private ?int $pageLength = null;
    private int $pageCurrent = 1;
    private ?string $sortColumn = null;
    private ?string $sortOrder = null;
    private array $filters = [];
    private array $quickSearch = [];
    private ?int $gridConfigId = null;
    private string $routeName = '';
    private array $routeParameters = [];
    private ?GridConfigEntity $gridConfig = null;

    public function __construct(
        SymfonyRequest $request,
        RouterInterface $router,
        GridDefinition $definition
    ) {
        $this->request = $request;
        $this->definition = $definition;
        $this->router = $router;
    }

    public function setRoute(string $routeName, array $routeParameters): void
    {
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
        $this->sessionPrefixGridKey = implode(
            '.',
            [
                'spipu.ui.grid',
                $this->definition->getCode(),
                implode('-', array_merge([$this->routeName], $this->routeParameters))
            ]
        );
    }

    public function prepare(): void
    {
        $this->preparePager();
        $this->prepareSort();
        $this->prepareFilters();
        $this->prepareQuickSearch();
    }

    private function getSessionKey(string $key): string
    {
        return $this->sessionPrefixGridKey . '.' . $key;
    }

    public function getSessionValue(string $key, mixed $default): mixed
    {
        return $this->request->getSession()->get($this->getSessionKey($key), $default);
    }

    private function setSessionValue(string $key, mixed $value): self
    {
        $this->request->getSession()->set($this->getSessionKey($key), $value);

        return $this;
    }

    private function removeSessionValue(string $key): self
    {
        $this->request->getSession()->remove($this->getSessionKey($key));

        return $this;
    }

    private function preparePager(): void
    {
        $this->pageCurrent = 1;
        $this->pageLength = self::MAX_AUTHORIZED_PAGE_LENGTH;

        if ($this->definition->getPager()) {
            $this->pageLength = (int) $this->getSessionValue('page_length', $this->pageLength);
            $this->pageLength = (int) $this->request->get(self::KEY_PAGE_LENGTH, $this->pageLength);

            $this->pageCurrent = (int) $this->getSessionValue('page_current', $this->pageCurrent);
            $this->pageCurrent = (int) $this->request->get(self::KEY_PAGE_CURRENT, $this->pageCurrent);

            if (!in_array($this->pageLength, $this->definition->getPager()->getLengths(), true)) {
                $this->pageLength = $this->definition->getPager()->getDefaultLength();
            }

            if ($this->pageCurrent < 1) {
                $this->pageCurrent = 1;
            }

            $this->setSessionValue('page_length', $this->pageLength);
            $this->setSessionValue('page_current', $this->pageCurrent);
        }
    }

    private function prepareSort(): void
    {
        $this->sortColumn = ($this->gridConfig ? ($this->gridConfig->getConfigSortColumn() ?? '') : '');
        $this->sortColumn = (string) $this->getSessionValue('sort_column', $this->sortColumn);
        $this->sortColumn = (string) $this->request->get(self::KEY_SORT_COLUMN, $this->sortColumn);

        $this->sortOrder = ($this->gridConfig ? ($this->gridConfig->getConfigSortOrder() ?? '') : '');
        $this->sortOrder = (string) $this->getSessionValue('sort_order', $this->sortOrder);
        $this->sortOrder = (string) $this->request->get(self::KEY_SORT_ORDER, $this->sortOrder);

        if ($this->sortColumn === '') {
            $this->sortColumn = $this->definition->getDefaultSortColumn();
        }

        if (!in_array($this->sortOrder, ['asc', 'desc'], true)) {
            $this->sortOrder = $this->definition->getDefaultSortOrder();
        }

        if ($this->sortColumn) {
            $column = $this->definition->getColumn($this->sortColumn);
            if ($column === null || !$column->isSortable()) {
                $this->sortColumn = null;
                $this->sortOrder = null;
            }
        }

        $this->setSessionValue('sort_column', $this->sortColumn);
        $this->setSessionValue('sort_order', $this->sortOrder);
    }

    public function getConfigParams(): ?array
    {
        $params = (array) $this->request->get(self::KEY_CONFIG, []);

        $this->gridConfigId = $this->getSessionValue('config_id', null);
        if (array_key_exists('id', $params) && is_numeric($params['id'])) {
            $this->updateCurrentConfigId((int) $params['id']);
        }
        $this->gridConfigId = (int) $this->gridConfigId;

        if (empty($params) || !array_key_exists('action', $params) || !is_string($params['action'])) {
            return null;
        }

        return $params;
    }

    public function getGridConfigId(): ?int
    {
        return $this->gridConfigId;
    }

    public function updateCurrentConfigId(int $gridConfigId): void
    {
        $this->gridConfigId = $gridConfigId;

        $this
            ->setSessionValue('config_id', $gridConfigId)
            ->removeSessionValue('sort_column')
            ->removeSessionValue('sort_order')
            ->removeSessionValue('page_current')
            ->removeSessionValue('filters')
        ;
    }

    public function setCurrentConfig(?GridConfigEntity $gridConfig): void
    {
        $this->gridConfig = $gridConfig;
    }

    private function prepareFilters(): void
    {
        $this->filters = ($this->gridConfig ? $this->gridConfig->getConfigFilters() : []);
        $this->filters = $this->getSessionValue('filters', $this->filters);
        $this->filters = (array) $this->request->get(self::KEY_FILTERS, $this->filters);

        foreach ($this->filters as $key => $value) {
            $column = $this->definition->getColumn($key);
            if ($column === null || !$column->getFilter()->isFilterable()) {
                unset($this->filters[$key]);
                continue;
            }

            if ($value === null) {
                unset($this->filters[$key]);
                continue;
            }

            if (!$column->getFilter()->isRange()) {
                $this->filters[$key] = trim((string) $value);
                if ($this->filters[$key] === '') {
                    unset($this->filters[$key]);
                }
                continue;
            }

            $this->filters[$key] = $this->validateRangeFilter($value);
            if (empty($this->filters[$key])) {
                unset($this->filters[$key]);
                continue;
            }
        }

        $this->setSessionValue('filters', $this->filters);
    }

    private function prepareQuickSearch(): void
    {
        $this->quickSearch = [];
        $this->quickSearch = $this->getSessionValue('quick_search', $this->quickSearch);
        $this->quickSearch = (array) $this->request->get(self::KEY_QUICK_SEARCH, $this->quickSearch);

        if (!array_key_exists('field', $this->quickSearch) || !array_key_exists('value', $this->quickSearch)) {
            $this->quickSearch = [];
            $this->setSessionValue('quick_search', $this->quickSearch);
            return;
        }

        $this->quickSearch['field'] = (string) $this->quickSearch['field'];
        $this->quickSearch['value'] = trim((string) $this->quickSearch['value']);


        $column = $this->definition->getColumn($this->quickSearch['field']);
        if ($column === null || !$column->getFilter()->isQuickSearch() || $this->quickSearch['value'] === '') {
            $this->quickSearch = [];
            $this->setSessionValue('quick_search', $this->quickSearch);
            return;
        }

        $this->setSessionValue('quick_search', $this->quickSearch);
        if (!empty($this->quickSearch)) {
            $this->filters = [];
            $this->setSessionValue('filters', $this->filters);
        }
    }

    private function validateRangeFilter(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $valueF = null;
        if (array_key_exists('from', $value)) {
            $valueF = $value['from'];
            if ($valueF !== null) {
                $valueF = trim((string) $valueF);
            }
            if ($valueF === '') {
                $valueF = null;
            }
        }

        $valueT = null;
        if (array_key_exists('to', $value)) {
            $valueT = $value['to'];
            if ($valueT !== null) {
                $valueT = trim((string) $valueT);
            }
            if ($valueT === '') {
                $valueT = null;
            }
        }

        $value = [];
        if ($valueF !== null) {
            $value['from'] = $valueF;
        }

        if ($valueT !== null) {
            $value['to'] = $valueT;
        }

        return $value;
    }

    public function getPageLength(): ?int
    {
        return $this->pageLength;
    }

    public function getPageCurrent(): int
    {
        return $this->pageCurrent;
    }

    public function forcePageCurrent(int $pageCurrent): self
    {
        if ($this->definition->getPager()) {
            if ($pageCurrent < 1) {
                $pageCurrent = 1;
            }

            $this->pageCurrent = $pageCurrent;
            $this->setSessionValue('page_current', $this->pageCurrent);
        }

        return $this;
    }

    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $key, string $subKey = null): string
    {
        if (!array_key_exists($key, $this->filters)) {
            return '';
        }

        if ($subKey === null) {
            return $this->filters[$key];
        }

        if (!is_array($this->filters[$key])) {
            return '';
        }

        if (!array_key_exists($subKey, $this->filters[$key])) {
            return '';
        }

        return $this->filters[$key][$subKey];
    }


    public function getQuickSearchField(): ?string
    {
        if (!array_key_exists('field', $this->quickSearch)) {
            return null;
        }

        return $this->quickSearch['field'];
    }

    public function getQuickSearchValue(): ?string
    {
        if (!array_key_exists('value', $this->quickSearch)) {
            return null;
        }

        return $this->quickSearch['value'];
    }

    public function getCurrentParams(): array
    {
        return [
            self::KEY_PAGE_CURRENT => $this->pageCurrent,
            self::KEY_PAGE_LENGTH  => $this->pageLength,
            self::KEY_SORT_COLUMN  => $this->sortColumn,
            self::KEY_SORT_ORDER   => $this->sortOrder,
            self::KEY_FILTERS      => $this->filters,
        ];
    }
    /**
     * @param array $params
     * @return string
     */
    public function getCurrentResetUrl(array $params): string
    {
        return $this->getCurrentUrl($params + [self::KEY_PAGE_CURRENT => 1, self::KEY_FILTERS => []]);
    }

    public function getCurrentUrl(array $params): string
    {
        $requestParams = $this->getCurrentParams();

        $params = array_merge($this->routeParameters, $requestParams, $params);

        return $this->router->generate($this->routeName, $params);
    }

    public function getSymfonyRequest(): SymfonyRequest
    {
        return $this->request;
    }
}
