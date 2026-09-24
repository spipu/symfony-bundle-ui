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
| `setCssClass(?string $cssClass)` | Custom CSS class on the menu item (on the main item, applied to the navbar itself, see [Styling and color mode](#styling-and-color-mode)) |

### Rendering the menu in Twig

The `getMenu` Twig function (from `UiExtension`) builds the menu and marks the active item:

```twig
{% set menu = getMenu('current-item-code') %}
{{ include('@SpipuUi/menu.html.twig') }}
```

Pass the `code` of the currently active item to `getMenu()`. The `Manager` service traverses the tree, evaluates access rules, and marks items as `allowed` or `active`.

### Styling and color mode

The global color mode is set on the `<html>` tag of `@SpipuUi/base.html.twig` (`light` by default). Override the `html_theme` block to change it:

```twig
{% extends '@SpipuUi/base.html.twig' %}

{% block html_theme %}dark{% endblock %}
```

By default, the navbar uses the theme-adaptive `bg-body-tertiary` background: it follows the global color mode, like its dropdowns.

To use a custom background, set it with `setCssClass()` on the main item, together with a contrast class matching this background:

| Class | Use with |
|-------|----------|
| `spipu-navbar-on-dark` | dark backgrounds (`bg-dark`, `bg-primary`, ...): light navbar content |
| `spipu-navbar-on-light` | light backgrounds (`bg-light`, `bg-warning`, ...): dark navbar content |

```php
$root = new Item('My App', '', 'app_home');
$root->setCssClass('bg-dark spipu-navbar-on-dark');
```

These classes only change the navbar content colors (links, brand, toggler): dropdown menus keep the global color mode.

### `Menu\Manager` access rules

- (not called) — always visible (default)
- `setACL(false)` — visible only when the user is **not** authenticated
- `setACL(true)` — visible only when authenticated
- `setACL(true, 'ROLE_FOO')` — visible only when authenticated and granted `ROLE_FOO`

A parent item with no route inherits its `allowed` state from its children (it is allowed if at least one child is allowed).

[back](./README.md)
