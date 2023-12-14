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
    private bool $filterable;
    private bool $quickSearch;
    private bool $range;
    private bool $exactValue;
    private ?string $templateFilter = null;

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

    public function linkToColumnType(ColumnType $columnType): bool
    {
        if ($this->templateFilter !== null) {
            return true;
        }

        $this->templateFilter = '@SpipuUi/grid/filter/' . $columnType->getType() . '.html.twig';
        return true;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function isQuickSearch(): bool
    {
        return $this->quickSearch;
    }

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

    public function getTemplateFilter(): string
    {
        return $this->templateFilter;
    }

    public function setTemplateFilter(string $templateFilter): self
    {
        $this->templateFilter = $templateFilter;

        return $this;
    }

    public function useFilterable(bool $filterable): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function useQuickSearch(bool $quickSearch): self
    {
        $this->quickSearch = $quickSearch;

        return $this;
    }
}
