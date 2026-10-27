# Spipu UI Bundle

The **UiBundle** provides a reusable admin UI framework built on Twig, Bootstrap 5, and Symfony Form. It includes a powerful data-grid system (with filtering, sorting, pagination, and row actions), a form/view management layer, a hierarchical menu system, options lists, and common Twig extensions.

## Documentation

- [Installation](./install.md)
- [Menu System](./menu.md)
- [Grid System](./grid.md)
- [Form / View / Menu System](./form.md)

## Features

- **Grid / List UI** — paginated, sortable, filterable Doctrine-backed data grids with configurable columns, row/mass/global actions, and optional per-user column personalization
- **Form Management** — structured form definitions (`Form` / `FieldSet` / `Field`) with Symfony form type integration, lifecycle events, and automatic entity persistence
- **Read-Only View** — same definition used by `ShowFactory` to render a read-only entity view
- **Menu System** — hierarchical admin navigation menu with role-based access control
- **Options System** — `OptionsInterface` / `AbstractOptions` for controlled key-label lists used in grids and forms
- **Personal data protection** — one rule per entity hides its personal data in grids, show pages and templates, excludes hidden entities from the grid filters and quick search, and denies their edition (see [Grid System](./grid.md#personal-data-protection))
- **Twig Extensions** — `renderManager`, `getMenu`, `getTranslations`, `isClosure`, `executeClosure`, `is_personal_data_hidden_entity`, `is_personal_data_hidden_field` functions; `label_from_option`, `label_from_option_name` filters; `personal_data.display` macro
- **Entity Traits** — `EntityInterface`, `TimestampableTrait` / `TimestampableInterface` for Doctrine entities
- **Custom Form Types** — `IntegerUnitType` and `NumberUnitType` for inputs with unit suffixes
- **Console Command** — `spipu:ui:grid-config:reset` to clear personalized grid configurations

## Requirements

- PHP >= 8.3
- Symfony >= 7.4
- `spipu/core-bundle`
- Bootstrap v5 (provided via the `spipu.asset` system)
- FontAwesome v7 (provided via the `spipu.asset` system)
- jQuery v4 (provided via the `spipu.asset` system)

## Quick Start

See [Installation](./install.md) for setup instructions, then [Grid System](./grid.md) or [Form / View / Menu System](./form.md) for usage.
