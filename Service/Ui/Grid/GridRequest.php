<?php
/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Spipu\UiBundle\Service\Ui\Grid;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PMD.CyclomaticComplexity)
 * @SuppressWarnings(PMD.NPathComplexity)
 */
class GridRequest
{
    const MAX_AUTHORIZED_PAGE_LENGTH = 10000;

    const KEY_PAGE_LENGTH  = 'pl';
    const KEY_PAGE_CURRENT = 'pc';
    const KEY_SORT_COLUMN  = 'sc';
    const KEY_SORT_ORDER   = 'so';
    const KEY_FILTERS      = 'fl';

    /**
     * @var SymfonyRequest
     */
    private $request;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var GridDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $sessionPrefixGridKey;

    /**
     * @var int|null
     */
    private $pageLength = null;

    /**
     * @var int
     */
    private $pageCurrent = 1;

    /**
     * @var string|null
     */
    private $sortColumn = null;

    /**
     * @var string|null
     */
    private $sortOrder = null;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * Request constructor.
     * @param SymfonyRequest $request
     * @param SessionInterface $session
     * @param RouterInterface $router
     * @param GridDefinition $definition
     */
    public function __construct(
        SymfonyRequest $request,
        SessionInterface $session,
        RouterInterface $router,
        GridDefinition $definition
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->definition = $definition;
        $this->router = $router;
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return void
     */
    public function prepare(string $routeName, array $routeParameters): void
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

        $this->preparePager();
        $this->prepareSort();
        $this->prepareFilters();
    }

    /**
     * @param string $key
     * @return string
     */
    private function getSessionKey(string $key): string
    {
        return $this->sessionPrefixGridKey.'.'.$key;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSessionValue(string $key, $default)
    {
        return $this->session->get($this->getSessionKey($key), $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return GridRequest
     */
    private function setSessionValue(string $key, $value): self
    {
        $this->session->set($this->getSessionKey($key), $value);

        return $this;
    }

    /**
     * @return void
     */
    private function preparePager(): void
    {
        $this->pageCurrent = 1;
        $this->pageLength = self::MAX_AUTHORIZED_PAGE_LENGTH;

        if ($this->definition->getPager()) {
            $this->pageLength = (int) $this->getSessionValue('page_length', $this->pageLength);
            $this->pageLength = (int) $this->request->get(self::KEY_PAGE_LENGTH, $this->pageLength);

            $this->pageCurrent = (int) $this->getSessionValue('page_current', $this->pageCurrent);
            $this->pageCurrent = (int) $this->request->get(self::KEY_PAGE_CURRENT, $this->pageCurrent);

            if (!in_array($this->pageLength, $this->definition->getPager()->getLengths())) {
                $this->pageLength = $this->definition->getPager()->getDefaultLength();
            }

            if ($this->pageCurrent < 1) {
                $this->pageCurrent = 1;
            }

            $this->setSessionValue('page_length', $this->pageLength);
            $this->setSessionValue('page_current', $this->pageCurrent);
        }
    }

    /**
     * @return void
     */
    private function prepareSort(): void
    {
        $this->sortColumn = '';
        $this->sortColumn = (string) $this->getSessionValue('sort_column', $this->sortColumn);
        $this->sortColumn = (string) $this->request->get(self::KEY_SORT_COLUMN, $this->sortColumn);

        $this->sortOrder = '';
        $this->sortOrder = (string) $this->getSessionValue('sort_order', $this->sortOrder);
        $this->sortOrder = (string) $this->request->get(self::KEY_SORT_ORDER, $this->sortOrder);

        if ($this->sortColumn === '') {
            $this->sortColumn = $this->definition->getDefaultSortColumn();
        }

        if (!in_array($this->sortOrder, ['asc', 'desc'])) {
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

    /**
     * @return void
     */
    private function prepareFilters(): void
    {
        $this->filters = [];
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

    /**
     * @param mixed $value
     * @return array
     */
    private function validateRangeFilter($value): array
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
        };

        $valueT = null;
        if (array_key_exists('to', $value)) {
            $valueT = $value['to'];
            if ($valueT !== null) {
                $valueT = trim((string) $valueT);
            }
            if ($valueT === '') {
                $valueT = null;
            }
        };

        $value = [];
        if ($valueF !== null) {
            $value['from'] = $valueF;
        }

        if ($valueT !== null) {
            $value['to'] = $valueT;
        }

        return $value;
    }

    /**
     * @return int|null
     */
    public function getPageLength(): ?int
    {
        return $this->pageLength;
    }

    /**
     * @return int
     */
    public function getPageCurrent(): int
    {
        return $this->pageCurrent;
    }

    /**
     * @param int $pageCurrent
     * @return GridRequest
     */
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

    /**
     * @return null|string
     */
    public function getSortColumn(): ?string
    {
        return $this->sortColumn;
    }

    /**
     * @return null|string
     */
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

    /**
     * @param string $key
     * @param string|null $subKey
     * @return string
     */
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

    /**
     * @param array $params
     * @return string
     */
    public function getCurrentUrl(array $params): string
    {
        $requestParams = [
            self::KEY_PAGE_CURRENT => $this->pageCurrent,
            self::KEY_PAGE_LENGTH  => $this->pageLength,
            self::KEY_SORT_COLUMN  => $this->sortColumn,
            self::KEY_SORT_ORDER   => $this->sortOrder,
            self::KEY_FILTERS      => $this->filters,
        ];

        $params = array_merge($this->routeParameters, $requestParams, $params);

        return $this->router->generate($this->routeName, $params);
    }
}
