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

use Spipu\UiBundle\Entity\OptionsTrait;
use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

class Column implements PositionInterface
{
    use PositionTrait;
    use OptionsTrait;

    private string $code;
    private string $name;
    private string $entityField;
    private ColumnType $type;
    private ColumnFilter $filter;
    private bool $sortable = false;
    private bool $displayed = true;

    public function __construct(
        string $code,
        string $name,
        string $entityField,
        int $position
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->entityField = $entityField;

        $this->setPosition($position);
        $this->setType(new ColumnType());
        $this->setFilter(new ColumnFilter());
        $this->useSortable(false);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntityField(): string
    {
        return $this->entityField;
    }

    public function getType(): ColumnType
    {
        return $this->type;
    }

    public function setType(ColumnType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFilter(): ColumnFilter
    {
        return $this->filter;
    }

    public function setFilter(ColumnFilter $filter): self
    {
        $this->filter = $filter;
        $this->filter->linkToColumnType($this->type);

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @param bool $sortable
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useSortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setEntityField(string $entityField): self
    {
        $this->entityField = $entityField;

        return $this;
    }

    public function setDisplayed(bool $displayed): self
    {
        $this->displayed = $displayed;

        return $this;
    }

    public function isDisplayed(): bool
    {
        return $this->displayed;
    }
}
