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

use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

class Action implements PositionInterface
{
    use PositionTrait;

    private string $code;
    private string $name;
    private string $routeName;
    private array $routeParams;
    private ?string $cssClass = null;
    private ?string $icon = null;
    private array $conditions = [];
    private ?string $neededRole = null;

    /**
     * @var callable|null
     */
    private $buildCallback = null;

    /**
     * Action constructor.
     * @param string $code
     * @param string $name
     * @param int $position
     * @param string $routeName
     * @param array $routeParams
     */
    public function __construct(
        string $code,
        string $name,
        int $position,
        string $routeName,
        array $routeParams = []
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->routeName = $routeName;
        $this->routeParams = $routeParams;

        $this->setPosition($position);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function setCssClass(?string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setConditions(array $conditions): self
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getNeededRole(): ?string
    {
        return $this->neededRole;
    }

    public function setNeededRole(?string $neededRole): self
    {
        $this->neededRole = $neededRole;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getBuildCallback(): ?callable
    {
        return $this->buildCallback;
    }

    public function setBuildCallback(?callable $buildCallback): self
    {
        $this->buildCallback = $buildCallback;

        return $this;
    }
}
