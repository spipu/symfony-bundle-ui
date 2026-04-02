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

use Closure;

class ColumnFilter
{
    private bool $filterable;
    private bool $quickSearch;
    private bool $range;
    private bool $exactValue;
    private bool $multipleValues;
    private ?string $templateFilter = null;
    private ?string $columnType = null;
    private ?Closure $valueTransformer = null;

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
        $this->multipleValues = false;
    }

    public function linkToColumnType(ColumnType $columnType): void
    {
        $this->columnType = $columnType->getType();
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
        $this->multipleValues = false;

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
        if ($this->templateFilter !== null) {
            return $this->templateFilter;
        }

        $templateCode = $this->columnType;
        if ($this->multipleValues) {
            $templateCode .= '-multiple';
        }

        return '@SpipuUi/grid/filter/' . $templateCode . '.html.twig';
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

    public function isMultipleValues(): bool
    {
        return $this->multipleValues;
    }

    public function useMultipleValues(bool $multipleValues): ColumnFilter
    {
        $this->multipleValues = $multipleValues;
        $this->range = false;

        return $this;
    }

    public function getValueTransformer(): ?Closure
    {
        return $this->valueTransformer;
    }

    /**
     * Format: function(string $value): string
     */
    public function setValueTransformer(Closure $valueTransformer): self
    {
        $this->valueTransformer = $valueTransformer;

        return $this;
    }
}
