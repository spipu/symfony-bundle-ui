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

use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

class Action implements PositionInterface
{
    use PositionTrait;

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
    private $routeName;

    /**
     * @var array
     */
    private $routeParams;

    /**
     * @var string|null
     */
    private $cssClass = null;

    /**
     * @var string|null
     */
    private $icon = null;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * @var string|null
     */
    private $neededRole = null;

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
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @return null|string
     */
    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    /**
     * @param null|string $cssClass
     * @return self
     */
    public function setCssClass(?string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     * @return self
     */
    public function setConditions(array $conditions): self
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNeededRole(): ?string
    {
        return $this->neededRole;
    }

    /**
     * @param null|string $neededRole
     * @return self
     */
    public function setNeededRole(?string $neededRole): self
    {
        $this->neededRole = $neededRole;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param null|string $icon
     * @return self
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return callable|null
     */
    public function getBuildCallback(): ?callable
    {
        return $this->buildCallback;
    }

    /**
     * @param callable|null $buildCallback
     * @return self
     */
    public function setBuildCallback(?callable $buildCallback): self
    {
        $this->buildCallback = $buildCallback;

        return $this;
    }
}
