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

    private string $code;
    private ?string $entityName;
    private string $dataProviderPrimaryKey = 'id';
    private string $requestPrimaryKey = 'id';
    private string $dataProviderServiceName = 'Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine';
    private ?Pager $pager = null;
    private ?string $defaultSortColumn = null;
    private ?string $defaultSortOrder = null;
    private int $actionLimit = 1;

    /**
     * @var Column[]
     */
    private array $columns = [];

    /**
     * @var Action[]
     */
    private array $rowActions = [];

    /**
     * @var Action[]
     */
    private array $massActions = [];

    /**
     * @var Action[]
     */
    private array $globalActions = [];

    /**
     * @var bool
     */
    private bool $personalize = false;

    /**
     * @var string[]
     */
    private array $templates = [
        'all'     => '@SpipuUi/grid/all.html.twig',
        'header'  => '@SpipuUi/grid/header.html.twig',
        'filters' => '@SpipuUi/grid/filters.html.twig',
        'config'  => '@SpipuUi/grid/config.html.twig',
        'pager'   => '@SpipuUi/grid/pager.html.twig',
        'page'    => '@SpipuUi/grid/page.html.twig',
        'row'     => '@SpipuUi/grid/row.html.twig',
        'actions' => '@SpipuUi/grid/actions.html.twig',
    ];

    public function __construct(
        string $code,
        string $entityName = null
    ) {
        $this->code = $code;
        $this->entityName = $entityName;
    }

    public function setPrimaryKey(string $dataProviderPrimaryKey = 'id', string $requestPrimaryKey = 'id'): self
    {
        $this->dataProviderPrimaryKey = $dataProviderPrimaryKey;
        $this->requestPrimaryKey = $requestPrimaryKey;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function getPager(): ?Pager
    {
        return $this->pager;
    }

    public function setPager(?Pager $pager): self
    {
        $this->pager = $pager;

        return $this;
    }

    public function setDefaultSort(string $column, string $order = 'asc'): self
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new GridException('Unknown default sort column');
        }

        if (!in_array($order, ['asc', 'desc'], true)) {
            throw new GridException('Invalid default sort order');
        }

        $this->defaultSortColumn = $column;
        $this->defaultSortOrder = $order;

        return $this;
    }

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

    public function getColumn(string $key): ?Column
    {
        if (!array_key_exists($key, $this->columns)) {
            return null;
        }

        return $this->columns[$key];
    }

    public function removeColumn(string $key): self
    {
        if (array_key_exists($key, $this->columns)) {
            unset($this->columns[$key]);
        }

        return $this;
    }

    public function addRowAction(Action $action): self
    {
        $this->rowActions[$action->getCode()] = $action;

        return $this;
    }

    public function removeRowAction(string $key): self
    {
        if (array_key_exists($key, $this->rowActions)) {
            unset($this->rowActions[$key]);
        }

        return $this;
    }

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

    public function addMassAction(Action $action): self
    {
        $this->massActions[$action->getCode()] = $action;

        return $this;
    }

    public function removeMassAction(string $key): self
    {
        if (array_key_exists($key, $this->massActions)) {
            unset($this->massActions[$key]);
        }

        return $this;
    }

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

    public function addGlobalAction(Action $action): self
    {
        $this->globalActions[$action->getCode()] = $action;

        return $this;
    }

    public function removeGlobalAction(string $key): self
    {
        if (array_key_exists($key, $this->globalActions)) {
            unset($this->globalActions[$key]);
        }

        return $this;
    }

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

    public function getTemplateAll(): string
    {
        return $this->templates['all'];
    }

    public function setTemplateAll(string $templateAll): self
    {
        $this->templates['all'] = $templateAll;

        return $this;
    }

    public function getTemplateHeader(): string
    {
        return $this->templates['header'];
    }

    public function setTemplateHeader(string $templateFilters): self
    {
        $this->templates['header'] = $templateFilters;

        return $this;
    }

    public function getTemplateFilters(): string
    {
        return $this->templates['filters'];
    }

    public function setTemplateFilters(string $templateFilters): self
    {
        $this->templates['filters'] = $templateFilters;

        return $this;
    }

    public function getTemplateConfig(): string
    {
        return $this->templates['config'];
    }

    public function setTemplateConfig(string $templateConfig): self
    {
        $this->templates['config'] = $templateConfig;

        return $this;
    }

    public function getTemplatePager(): string
    {
        return $this->templates['pager'];
    }

    public function setTemplatePager(string $templatePager): self
    {
        $this->templates['pager'] = $templatePager;

        return $this;
    }

    public function getTemplateRow(): string
    {
        return $this->templates['row'];
    }

    public function setTemplateRow(string $templateRow): self
    {
        $this->templates['row'] = $templateRow;

        return $this;
    }

    public function getTemplatePage(): string
    {
        return $this->templates['page'];
    }

    public function setTemplatePage(string $templatePage): self
    {
        $this->templates['page'] = $templatePage;

        return $this;
    }

    public function getDefaultSortColumn(): ?string
    {
        return $this->defaultSortColumn;
    }

    public function getDefaultSortOrder(): ?string
    {
        return $this->defaultSortOrder;
    }

    public function getTemplateActions(): string
    {
        return $this->templates['actions'];
    }

    public function setTemplateActions(string $templateActions): self
    {
        $this->templates['actions'] = $templateActions;

        return $this;
    }

    public function getDataProviderServiceName(): string
    {
        return $this->dataProviderServiceName;
    }

    public function setDataProviderServiceName(string $dataProviderServiceName): self
    {
        $this->dataProviderServiceName = $dataProviderServiceName;

        return $this;
    }

    public function getDataProviderPrimaryKey(): string
    {
        return $this->dataProviderPrimaryKey;
    }

    public function getRequestPrimaryKey(): string
    {
        return $this->requestPrimaryKey;
    }

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

    public function isPersonalize(): bool
    {
        return $this->personalize;
    }

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
            if (
                $column->getFilter()->isFilterable()
                && !$column->getFilter()->isRange()
                && $column->getType()->getType() === ColumnType::TYPE_SELECT
            ) {
                $columns[$column->getCode()] = $column;
            }
        }

        return $columns;
    }

    public function setActionLimit(int $actionLimit): Grid
    {
        $this->actionLimit = $actionLimit;
        return $this;
    }

    public function getActionLimit(): int
    {
        return $this->actionLimit;
    }
}
