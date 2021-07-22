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

class ColumnFilter
{
    /**
     * @var bool
     */
    private $filterable;

    /**
     * @var bool
     */
    private $quickSearch;

    /**
     * @var bool
     */
    private $range;

    /**
     * @var bool
     */
    private $exactValue;

    /**
     * @var string|null
     */
    private $templateFilter;

    /**
     * Pager constructor.
     * @param bool $filterable
     * @param bool $quickSearch
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function __construct(
        bool $filterable = false,
        bool $quickSearch = false
    ) {
        $this->filterable = $filterable;
        $this->quickSearch = $quickSearch;
        $this->range = false;
        $this->exactValue = false;
    }

    /**
     * @param ColumnType $columnType
     * @return bool
     */
    public function linkToColumnType(ColumnType $columnType): bool
    {
        if ($this->templateFilter !== null) {
            return true;
        }

        $this->templateFilter = '@SpipuUi/grid/filter/'.$columnType->getType().'.html.twig';
        return true;
    }

    /**
     * @return bool
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * @return bool
     */
    public function isQuickSearch(): bool
    {
        return $this->quickSearch;
    }

    /**
     * @return bool
     */
    public function isRange(): bool
    {
        return $this->range;
    }

    /**
     * @param bool $range
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useRange(bool $range = true): self
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExactValue(): bool
    {
        return $this->exactValue;
    }

    /**
     * @param bool $exactValue
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useExactValue(bool $exactValue = true): self
    {
        $this->exactValue = $exactValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateFilter(): string
    {
        return $this->templateFilter;
    }

    /**
     * @param string $templateFilter
     *
     * @return self
     */
    public function setTemplateFilter(string $templateFilter): self
    {
        $this->templateFilter = $templateFilter;

        return $this;
    }

    /**
     * @param bool $filterable
     * @return self
     */
    public function useFilterable(bool $filterable): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * @param bool $quickSearch
     * @return self
     */
    public function useQuickSearch(bool $quickSearch): self
    {
        $this->quickSearch = $quickSearch;

        return $this;
    }
}
