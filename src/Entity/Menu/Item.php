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

namespace Spipu\UiBundle\Entity\Menu;

/**
 * @SuppressWarnings(PMD.TooManyFields)
 */
class Item
{
    private static int $lastId = 0;
    private int $id;
    private ?string $name;
    private ?string $code;
    private ?string $route;
    private array $routeParams;
    private ?bool $connected;
    private ?string $role;
    private bool $allowed;
    private bool $active;
    private ?string $icon;
    private ?string $iconThemeColor;
    private ?string $iconTitle;
    private ?string $cssClass;
    private ?Item $parentItem;

    /**
     * @var Item[]
     */
    private array $childItems;

    public function __construct(
        string $name,
        ?string $code = null,
        ?string $route = null,
        array $routeParams = []
    ) {
        self::$lastId++;

        $this->id = self::$lastId;
        $this->name = $name;
        $this->code = $code;
        $this->route = $route;
        $this->routeParams = $routeParams;
        $this->connected = null;
        $this->role = null;
        $this->childItems = [];
        $this->parentItem = null;
        $this->active = false;
        $this->allowed = false;
        $this->icon = null;
        $this->iconThemeColor = null;
        $this->iconTitle = null;
        $this->cssClass = null;
    }

    public function setACL(bool $connected, ?string $role = null): self
    {
        if ($role !== null) {
            $connected = true;
        }

        $this->connected = $connected;
        $this->role = $role;

        return $this;
    }

    public function addChild(
        string $name,
        ?string $code = null,
        ?string $route = null,
        array $routeParams = []
    ): Item {
        $item = new Item($name, $code, $route, $routeParams);

        $this->addChildItem($item);

        return $item;
    }

    protected function setParentItem(Item $item): void
    {
        $this->parentItem = $item;
    }

    public function addChildItem(Item $item): void
    {
        $item->setParentItem($this);

        $this->childItems[] = $item;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getConnected(): ?bool
    {
        return $this->connected;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @return Item[]
     */
    public function getChildItems(): array
    {
        return $this->childItems;
    }

    public function getParentItem(): ?Item
    {
        return $this->parentItem;
    }

    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;

        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getIconThemeColor(): ?string
    {
        return $this->iconThemeColor;
    }

    public function getIconTitle(): ?string
    {
        return $this->iconTitle;
    }

    public function setIcon(string $icon, string $iconThemeColor = 'secondary', ?string $iconTitle = null): self
    {
        $this->icon = $icon;
        $this->iconThemeColor = $iconThemeColor;
        $this->iconTitle = $iconTitle;

        return $this;
    }

    public static function resetAll(): void
    {
        self::$lastId = 0;
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
}
