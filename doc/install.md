# Installing Spipu UI Bundle

[back](./README.md)

## Requirements

- PHP >= 8.3
- Symfony >= 7.4
- `spipu/core-bundle`

## Installation

```bash
composer require spipu/ui-bundle
```

## Configuration

### 1. Register the bundle

In `config/bundles.php`:

```php
return [
    // ...
    Spipu\CoreBundle\SpipuCoreBundle::class => ['all' => true],
    Spipu\UiBundle\SpipuUiBundle::class => ['all' => true],
];
```

### 2. Install assets

The bundle ships asset definitions for Bootstrap 4, jQuery, Popper, and FontAwesome via `spipu/core-bundle`'s asset system. Run:

```bash
php bin/console spipu:assets:install
```

Or install them via your own asset pipeline (Webpack Encore, etc.).

### 3. Database migration

The bundle provides a `spipu_ui_grid_config` Doctrine entity (used for personalized grid column configurations). Add the table via migrations:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 4. Menu definition

The bundle registers a default (empty) menu definition as `spipu.ui.service.menu_definition`. Override it by creating your own class implementing `Spipu\UiBundle\Service\Menu\DefinitionInterface` and registering it in `services.yaml`:

```yaml
# config/services.yaml
spipu.ui.service.menu_definition:
    class: App\Service\MenuDefinition
```

### 5. Twig form theme

`SpipuUiBundle` automatically prepends the Twig form theme `@SpipuUi/form_layout.html.twig` during its boot. No manual configuration is needed.

## Overriding Templates

All UiBundle templates can be overridden by creating files under `templates/bundles/SpipuUiBundle/`. For example, to override the main grid template:

```
templates/
  bundles/
    SpipuUiBundle/
      grid/
        all.html.twig
```

Available grid sub-templates: `all.html.twig`, `header.html.twig`, `filters.html.twig`, `config.html.twig`, `pager.html.twig`, `page.html.twig`, `row.html.twig`, `actions.html.twig`.

## Console Command

```bash
php bin/console spipu:ui:grid-config:reset
```

Deletes all saved personalized grid configurations from the database.

[back](./README.md)
