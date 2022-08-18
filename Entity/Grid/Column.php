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

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $entityField;

    /**
     * @var ColumnType
     */
    private $type;

    /**
     * @var ColumnFilter
     */
    private $filter;

    /**
     * @var bool
     */
    private $sortable = false;

    /**
     * @var bool
     */
    private $displayed = true;

    /**
     * Column constructor.
     * @param string $code
     * @param string $name
     * @param string $entityField
     * @param int $position
     */
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

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityField(): string
    {
        return $this->entityField;
    }

    /**
     * @return ColumnType
     */
    public function getType(): ColumnType
    {
        return $this->type;
    }

    /**
     * @param ColumnType $type
     * @return self
     */
    public function setType(ColumnType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return ColumnFilter
     */
    public function getFilter(): ColumnFilter
    {
        return $this->filter;
    }

    /**
     * @param ColumnFilter $filter
     * @return self
     */
    public function setFilter(ColumnFilter $filter): self
    {
        $this->filter = $filter;
        $this->filter->linkToColumnType($this->type);

        return $this;
    }

    /**
     * @return bool
     */
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

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $entityField
     * @return self
     */
    public function setEntityField(string $entityField): self
    {
        $this->entityField = $entityField;

        return $this;
    }

    /**
     * @param bool $displayed
     * @return Column
     */
    public function setDisplayed(bool $displayed): self
    {
        $this->displayed = $displayed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->displayed;
    }
}
