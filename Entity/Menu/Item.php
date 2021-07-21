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

class Item
{
    /**
     * @var int
     */
    private static $lastId = 0;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $route;

    /**
     * @var array
     */
    private $routeParams;

    /**
     * @var bool|null
     */
    private $connected;

    /**
     * @var string|null
     */
    private $role;

    /**
     * @var bool
     */
    private $allowed;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var Item[]
     */
    private $childItems;

    /**
     * @var Item|null
     */
    private $parentItem;

    /**
     * @var string|null
     */
    private $icon = null;

    /**
     * Item constructor.
     * @param string $name
     * @param null|string $code
     * @param null|string $route
     * @param array $routeParams
     */
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
    }

    /**
     * @param bool $connected
     * @param string|null $role
     * @return self
     */
    public function setACL(bool $connected, ?string $role = null): self
    {
        if ($role !== null) {
            $connected = true;
        }

        $this->connected = $connected;
        $this->role = $role;

        return $this;
    }


    /**
     * @param string $name
     * @param null|string $code
     * @param null|string $route
     * @param array $routeParams
     * @return Item
     */
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

    /**
     * @param Item $item
     * @return void
     */
    protected function setParentItem(Item $item): void
    {
        $this->parentItem = $item;
    }

    /**
     * @param Item $item
     * @return void
     */
    public function addChildItem(Item $item): void
    {
        $item->setParentItem($this);

        $this->childItems[] = $item;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return null|string
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @return bool|null
     */
    public function getConnected(): ?bool
    {
        return $this->connected;
    }

    /**
     * @return null|string
     */
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

    /**
     * @return null|Item
     */
    public function getParentItem(): ?Item
    {
        return $this->parentItem;
    }

    /**
     * @param bool $allowed
     * @return self
     */
    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * @param bool $active
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return void
     */
    public static function resetAll(): void
    {
        self::$lastId = 0;
    }
}
