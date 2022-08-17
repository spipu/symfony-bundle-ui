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

namespace Spipu\UiBundle\Entity\Grid;

use Spipu\UiBundle\Entity\GridConfig;
use Spipu\UiBundle\Entity\OptionsTrait;
use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Exception\GridException;

/**
 * Class Grid
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PMD.ExcessivePublicCount)
 */
class Grid
{
    use OptionsTrait;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string|null
     */
    private $entityName;

    /**
     * @var string
     */
    private $dataProviderPrimaryKey = 'id';

    /**
     * @var string
     */
    private $requestPrimaryKey = 'id';

    /**
     * @var string
     */
    private $dataProviderServiceName = 'Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine';

    /**
     * @var Pager|null
     */
    private $pager = null;

    /**
     * @var string|null
     */
    private $defaultSortColumn = null;

    /**
     * @var string|null
     */
    private $defaultSortOrder = null;

    /**
     * @var Column[]
     */
    private $columns = [];

    /**
     * @var Action[]
     */
    private $rowActions = [];

    /**
     * @var Action[]
     */
    private $massActions = [];

    /**
     * @var Action[]
     */
    private $globalActions = [];

    /**
     * @var bool
     */
    private $personalize = false;

    /**
     * @var string[]
     */
    private $templates = [
        'all'     => '@SpipuUi/grid/all.html.twig',
        'header'  => '@SpipuUi/grid/header.html.twig',
        'filters' => '@SpipuUi/grid/filters.html.twig',
        'config'  => '@SpipuUi/grid/config.html.twig',
        'pager'   => '@SpipuUi/grid/pager.html.twig',
        'page'    => '@SpipuUi/grid/page.html.twig',
        'row'     => '@SpipuUi/grid/row.html.twig',
        'actions' => '@SpipuUi/grid/actions.html.twig',
    ];

    /**
     * Grid constructor.
     * @param string $code
     * @param string|null $entityName
     */
    public function __construct(
        string $code,
        string $entityName = null
    ) {
        $this->code = $code;
        $this->entityName = $entityName;
    }

    /**
     * @param string $dataProviderPrimaryKey
     * @param string $requestPrimaryKey
     * @return Grid
     */
    public function setPrimaryKey(string $dataProviderPrimaryKey = 'id', string $requestPrimaryKey = 'id'): self
    {
        $this->dataProviderPrimaryKey = $dataProviderPrimaryKey;
        $this->requestPrimaryKey = $requestPrimaryKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    /**
     * @return Pager|null
     */
    public function getPager(): ?Pager
    {
        return $this->pager;
    }

    /**
     * @param Pager|null $pager
     * @return self
     */
    public function setPager(?Pager $pager): self
    {
        $this->pager = $pager;

        return $this;
    }

    /**
     * @param string $column
     * @param string $order
     * @return Grid
     * @throws GridException
     */
    public function setDefaultSort(string $column, string $order = 'asc'): self
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new GridException('Unknown default sort column');
        }

        if (!in_array($order, ['asc', 'desc'])) {
            throw new GridException('Invalid default sort order');
        }

        $this->defaultSortColumn = $column;
        $this->defaultSortOrder = $order;

        return $this;
    }

    /**
     * @param Column $column
     * @return Grid
     */
    public function addColumn(Column $column): self
    {
        $this->columns[$column->getCode()] = $column;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param string $key
     * @return null|Column
     */
    public function getColumn(string $key): ?Column
    {
        if (!array_key_exists($key, $this->columns)) {
            return null;
        }

        return $this->columns[$key];
    }

    /**
     * @param string $key
     * @return Grid
     */
    public function removeColumn(string $key): self
    {
        if (array_key_exists($key, $this->columns)) {
            unset($this->columns[$key]);
        }

        return $this;
    }

    /**
     * @param Action $action
     * @return Grid
     */
    public function addRowAction(Action $action): self
    {
        $this->rowActions[$action->getCode()] = $action;

        return $this;
    }

    /**
     * @param string $key
     * @return Grid
     */
    public function removeRowAction(string $key): self
    {
        if (array_key_exists($key, $this->rowActions)) {
            unset($this->rowActions[$key]);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return null|Action
     */
    public function getRowAction(string $key): ?Action
    {
        if (!array_key_exists($key, $this->rowActions)) {
            return null;
        }

        return $this->rowActions[$key];
    }

    /**
     * @return Action[]
     */
    public function getRowActions(): array
    {
        return $this->rowActions;
    }

    /**
     * @param Action $action
     * @return Grid
     */
    public function addMassAction(Action $action): self
    {
        $this->massActions[$action->getCode()] = $action;

        return $this;
    }

    /**
     * @param string $key
     * @return Grid
     */
    public function removeMassAction(string $key): self
    {
        if (array_key_exists($key, $this->massActions)) {
            unset($this->massActions[$key]);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return null|Action
     */
    public function getMassAction(string $key): ?Action
    {
        if (!array_key_exists($key, $this->massActions)) {
            return null;
        }

        return $this->massActions[$key];
    }

    /**
     * @return Action[]
     */
    public function getMassActions(): array
    {
        return $this->massActions;
    }

    /**
     * @param Action $action
     * @return Grid
     */
    public function addGlobalAction(Action $action): self
    {
        $this->globalActions[$action->getCode()] = $action;

        return $this;
    }

    /**
     * @param string $key
     * @return Grid
     */
    public function removeGlobalAction(string $key): self
    {
        if (array_key_exists($key, $this->globalActions)) {
            unset($this->globalActions[$key]);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return null|Action
     */
    public function getGlobalAction(string $key): ?Action
    {
        if (!array_key_exists($key, $this->globalActions)) {
            return null;
        }

        return $this->globalActions[$key];
    }

    /**
     * @return Action[]
     */
    public function getGlobalActions(): array
    {
        return $this->globalActions;
    }

    /**
     * @return string
     */
    public function getTemplateAll(): string
    {
        return $this->templates['all'];
    }

    /**
     * @param string $templateAll
     * @return self
     */
    public function setTemplateAll(string $templateAll): self
    {
        $this->templates['all'] = $templateAll;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateHeader(): string
    {
        return $this->templates['header'];
    }

    /**
     * @param string $templateFilters
     * @return self
     */
    public function setTemplateHeader(string $templateFilters): self
    {
        $this->templates['header'] = $templateFilters;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFilters(): string
    {
        return $this->templates['filters'];
    }

    /**
     * @param string $templateFilters
     * @return self
     */
    public function setTemplateFilters(string $templateFilters): self
    {
        $this->templates['filters'] = $templateFilters;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateConfig(): string
    {
        return $this->templates['config'];
    }

    /**
     * @param string $templateConfig
     * @return self
     */
    public function setTemplateConfig(string $templateConfig): self
    {
        $this->templates['config'] = $templateConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatePager(): string
    {
        return $this->templates['pager'];
    }

    /**
     * @param string $templatePager
     * @return self
     */
    public function setTemplatePager(string $templatePager): self
    {
        $this->templates['pager'] = $templatePager;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateRow(): string
    {
        return $this->templates['row'];
    }

    /**
     * @param string $templateRow
     * @return self
     */
    public function setTemplateRow(string $templateRow): self
    {
        $this->templates['row'] = $templateRow;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatePage(): string
    {
        return $this->templates['page'];
    }

    /**
     * @param string $templatePage
     * @return self
     */
    public function setTemplatePage(string $templatePage): self
    {
        $this->templates['page'] = $templatePage;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDefaultSortColumn(): ?string
    {
        return $this->defaultSortColumn;
    }

    /**
     * @return null|string
     */
    public function getDefaultSortOrder(): ?string
    {
        return $this->defaultSortOrder;
    }

    /**
     * @return string
     */
    public function getTemplateActions(): string
    {
        return $this->templates['actions'];
    }

    /**
     * @param string $templateActions
     * @return self
     */
    public function setTemplateActions(string $templateActions): self
    {
        $this->templates['actions'] = $templateActions;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataProviderServiceName(): string
    {
        return $this->dataProviderServiceName;
    }

    /**
     * @param string $dataProviderServiceName
     * @return $this
     */
    public function setDataProviderServiceName(string $dataProviderServiceName): self
    {
        $this->dataProviderServiceName = $dataProviderServiceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataProviderPrimaryKey(): string
    {
        return $this->dataProviderPrimaryKey;
    }

    /**
     * @return string
     */
    public function getRequestPrimaryKey(): string
    {
        return $this->requestPrimaryKey;
    }

    /**
     * @param string|null $entityName
     * @return self
     */
    public function setEntityName(?string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @param bool $personalize
     * @return Grid
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function setPersonalize(bool $personalize = false): Grid
    {
        $this->personalize = $personalize;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPersonalize(): bool
    {
        return $this->personalize;
    }

    /**
     * Sort the columns and the actions
     *
     * @return void
     */
    public function prepareSort(): void
    {
        uasort(
            $this->columns,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );

        uasort(
            $this->rowActions,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );

        uasort(
            $this->massActions,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );

        uasort(
            $this->globalActions,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );
    }

    /**
     * @param GridConfig|null $gridConfig
     * @return Column[]
     */
    public function getDisplayedColumns(?GridConfig $gridConfig = null): array
    {
        $columns = [];

        if ($gridConfig) {
            foreach ($gridConfig->getConfigColumns() as $columnKey) {
                $column = $this->getColumn($columnKey);
                if ($column !== null) {
                    $columns[] = $column;
                }
            }

            return $columns;
        }

        foreach ($this->columns as $column) {
            if ($column->isDisplayed()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * @return Column[]
     */
    public function getSortableColumns(): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column->isSortable()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * @return Column[]
     */
    public function getFilterableColumns(): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column->getFilter()->isFilterable()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * @return Column[]
     */
    public function getQuickSearchColumns(): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column->getFilter()->isQuickSearch()) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    /**
     * @return Column[]
     */
    public function getFilterableSelectColumns(): array
    {
        $columns = [];

        foreach ($this->columns as $column) {
            if ($column->getFilter()->isFilterable() && $column->getType()->getType() === ColumnType::TYPE_SELECT) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }
}
