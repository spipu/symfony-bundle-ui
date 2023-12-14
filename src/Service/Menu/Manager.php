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

namespace Spipu\UiBundle\Service\Menu;

use Spipu\UiBundle\Entity\Menu\Item;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Manager
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private DefinitionInterface $menuDefinition;
    private ?Item $mainMenuItem = null;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        DefinitionInterface $menuDefinition
    ) {
        $this->menuDefinition = $menuDefinition;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildMenu(string $currentItemCode = ''): Item
    {
        if (!$this->mainMenuItem) {
            $this->mainMenuItem = $this->menuDefinition->getDefinition();
            $this->validateItem($this->mainMenuItem, $currentItemCode);
        }

        return $this->mainMenuItem;
    }

    private function validateItem(Item $menuItem, string $currentItemCode): void
    {
        $menuItem->setActive(
            $menuItem->getCode() == $currentItemCode
        );

        $menuItem->setAllowed(
            $this->isAllowed($menuItem->getConnected(), $menuItem->getRole())
        );

        $allowed = false;
        $active  = false;
        foreach ($menuItem->getChildItems() as $childMenuItem) {
            $this->validateItem($childMenuItem, $currentItemCode);

            $allowed = ($allowed || $childMenuItem->isAllowed());
            $active  = ($active  || $childMenuItem->isActive());
        }
        if ($active) {
            $menuItem->setActive(true);
        }
        if ($menuItem->getRoute() === null) {
            $menuItem->setAllowed($allowed);
        }
    }

    private function isAllowed(?bool $connected, ?string $role): bool
    {
        if ($connected === null) {
            return true;
        }

        if ($connected === false) {
            return !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED');
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return false;
        }

        if ($role === null) {
            return true;
        }

        return $this->authorizationChecker->isGranted($role);
    }
}
