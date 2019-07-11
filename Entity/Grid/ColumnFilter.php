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

namespace Spipu\UiBundle\Entity\Grid;

class ColumnFilter
{
    /**
     * @var bool
     */
    private $filterable = false;

    /**
     * @var bool
     */
    private $range = false;

    /**
     * @var string|null
     */
    private $templateFilter;

    /**
     * Pager constructor.
     * @param bool $filterable
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function __construct(
        bool $filterable = false
    ) {
        $this->filterable = $filterable;
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
}
