# Menu System

[back](./README.md)

## Overview

The menu system provides hierarchical admin navigation. Implement `DefinitionInterface` to define the menu tree:

```php
use Spipu\UiBundle\Service\Menu\DefinitionInterface;
use Spipu\UiBundle\Entity\Menu\Item;

class MyMenuDefinition implements DefinitionInterface
{
    public function getDefinition(): Item
    {
        $root = new Item('My App', '', 'app_home');

        $root
            ->addChild('menu.products', 'products', 'app_product_index')
                ->setACL(true, 'ROLE_ADMIN_PRODUCT_SHOW')
                ->setIcon('box')
                ->getParentItem()
            ->addChild('menu.admin')
                ->addChild('menu.users', 'users', 'app_user_index')
                    ->setACL(true, 'ROLE_ADMIN_USER_SHOW')
                    ->getParentItem()
                ->getParentItem()
            ->addChild('menu.login', 'login', 'app_login')
                ->setACL(false)    // only show when NOT logged in
                ->getParentItem()
            ->addChild('menu.logout', 'logout', 'app_logout')
                ->setACL(true)     // only show when logged in
                ->getParentItem()
        ;

        return $root;
    }
}
```

Register it by overriding the bundle's default definition service:

```yaml
# config/services.yaml
spipu.ui.service.menu_definition:
    class: App\Service\MyMenuDefinition
    autowire: true
```

### `Item` constructor

```php
new Item(string $name, ?string $code = null, ?string $route = null, array $routeParams = [])
```

| Argument | Description |
|----------|-------------|
| `$name` | Display label or translation key |
| `$code` | Unique identifier used for active-item detection |
| `$route` | Symfony route name for the link |
| `$routeParams` | Route parameters |

### Key `Item` methods

| Method | Description |
|--------|-------------|
| `addChild(string $name, ?string $code, ?string $route, array $params): Item` | Add and return a child item |
| `addChildItem(Item $item): void` | Add a pre-built child item |
| `getParentItem(): ?Item` | Return the parent (for chaining back up) |
| `setACL(bool $connected, ?string $role = null)` | Set access control: `false` = guests only, `true` = authenticated users, `$role` = specific role |
| `setIcon(string $icon, string $iconThemeColor = 'secondary', ?string $iconTitle = null)` | Set FontAwesome icon name and Bootstrap theme color |
| `setCssClass(?string $cssClass)` | Custom CSS class on the menu item |

### Rendering the menu in Twig

The `getMenu` Twig function (from `UiExtension`) builds the menu and marks the active item:

```twig
{% set menu = getMenu('current-item-code') %}
{{ include('@SpipuUi/menu.html.twig') }}
```

Pass the `code` of the currently active item to `getMenu()`. The `Manager` service traverses the tree, evaluates access rules, and marks items as `allowed` or `active`.

### `Menu\Manager` access rules

- `setACL(null)` — always visible (default when `setACL` is not called)
- `setACL(false)` — visible only when the user is **not** authenticated
- `setACL(true)` — visible only when authenticated
- `setACL(true, 'ROLE_FOO')` — visible only when authenticated and granted `ROLE_FOO`

A parent item with no route inherits its `allowed` state from its children (it is allowed if at least one child is allowed).

[back](./README.md)
