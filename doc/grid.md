# Grid System

[back](./README.md)

## Overview

The grid system renders paginated, sortable, filterable data lists backed by a Doctrine query. A grid is defined by implementing `GridDefinitionInterface`, which builds and returns a `Grid` entity describing:

- the **Doctrine entity** to query
- the **columns** to display (with types and optional filters)
- the **pager** configuration
- the **row actions**, **mass actions**, and **global actions**

## Defining a Grid

```php
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Entity\Grid\Column;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Spipu\UiBundle\Entity\Grid\ColumnFilter;
use Spipu\UiBundle\Entity\Grid\Action;
use Spipu\UiBundle\Entity\Grid\Pager;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UiBundle\Form\Options\YesNo;

class UserGrid implements GridDefinitionInterface
{
    public function __construct(private YesNo $optionsYesNo) {}

    public function getDefinition(): Grid
    {
        return (new Grid('user', App\Entity\User::class))
            ->setPager(new Pager([10, 20, 50, 100], 20))
            ->addColumn(
                (new Column('id', 'user.field.id', 'id', 10))
                    ->setType(new ColumnType(ColumnType::TYPE_INTEGER))
                    ->setFilter((new ColumnFilter(true, true))->useRange())
                    ->useSortable()
            )
            ->addColumn(
                (new Column('username', 'user.field.username', 'username', 20))
                    ->setType(new ColumnType(ColumnType::TYPE_TEXT))
                    ->setFilter(new ColumnFilter(true, true))
                    ->useSortable()
            )
            ->addColumn(
                (new Column('active', 'user.field.active', 'active', 30))
                    ->setType(
                        (new ColumnType(ColumnType::TYPE_SELECT))
                            ->setOptions($this->optionsYesNo)
                            ->setTemplateField('@SpipuUi/grid/field/yes-no.html.twig')
                    )
                    ->setFilter(new ColumnFilter(true, false))
                    ->useSortable()
            )
            ->addColumn(
                (new Column('updatedAt', 'user.field.updated_at', 'updatedAt', 40))
                    ->setType(new ColumnType(ColumnType::TYPE_DATETIME))
                    ->setFilter((new ColumnFilter(true))->useRange())
                    ->useSortable()
            )
            ->setDefaultSort('id', 'asc')
            ->addRowAction(
                (new Action('show', 'spipu.ui.action.show', 10, 'app_user_show'))
                    ->setIcon('eye')
                    ->setCssClass('primary')
            )
            ->addRowAction(
                (new Action('edit', 'spipu.ui.action.edit', 20, 'app_user_edit'))
                    ->setIcon('pen-to-square')
                    ->setCssClass('success')
                    ->setNeededRole('ROLE_ADMIN_USER_EDIT')
            )
            ->addGlobalAction(
                (new Action('create', 'spipu.ui.action.create', 10, 'app_user_create'))
                    ->setIcon('pen-to-square')
                    ->setCssClass('success')
                    ->setNeededRole('ROLE_ADMIN_USER_EDIT')
            )
        ;
    }
}
```

## Grid Constructor

```php
new Grid(string $code, ?string $entityName = null)
```

| Argument | Description |
|----------|-------------|
| `$code` | Unique grid identifier string (used for session keys, events) |
| `$entityName` | Fully-qualified Doctrine entity class name |

### Key Grid methods

| Method | Description |
|--------|-------------|
| `setPager(Pager $pager)` | Enable pagination with the given `Pager` config |
| `setDefaultSort(string $column, string $order = 'asc')` | Default sort column (must be added and sortable) |
| `setPrimaryKey(string $dataProviderPK = 'id', string $requestPK = 'id')` | Override primary key field names |
| `setDataProviderServiceName(string $service)` | Override data provider (default: `Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine`) |
| `setPersonalize(bool $personalize = false)` | Enable per-user column configuration |
| `setActionLimit(int $limit)` | Max visible row actions before collapsing to a dropdown (default: 1) |
| `addColumn(Column $column)` | Add a column |
| `addRowAction(Action $action)` | Add a per-row action |
| `addMassAction(Action $action)` | Add a mass (bulk) action |
| `addGlobalAction(Action $action)` | Add a global (header) action |
| `setTemplateAll(string $template)` | Override the main grid template |

## Pager

```php
new Pager(array $lengths = [10, 20, 50, 100], int $defaultLength = 20)
```

`$lengths` is the list of page-size options shown in the UI. `$defaultLength` must be one of the values in `$lengths`.

If `setPager()` is not called on the Grid, all rows are shown without pagination.

## Column

```php
new Column(string $code, string $name, string $entityField, int $position)
```

| Argument | Description |
|----------|-------------|
| `$code` | Unique column key within the grid |
| `$name` | Translation key for the column header |
| `$entityField` | Entity property name (or dotted path for joined fields, e.g. `role.name`) |
| `$position` | Sort order for column display |

### Key Column methods

| Method | Description |
|--------|-------------|
| `setType(ColumnType $type)` | Set the display type (default: `TYPE_TEXT`) |
| `setFilter(ColumnFilter $filter)` | Set the filter configuration |
| `useSortable(bool $sortable = true)` | Enable/disable sorting for this column |
| `setDisplayed(bool $displayed)` | Whether the column is shown by default (default: `true`) |
| `addOption(string $key, mixed $value)` | Attach arbitrary options (e.g. `filter-css-class`) |

## Column Types (`ColumnType`)

`ColumnType` wraps the display type and template for a column.

```php
new ColumnType(string $type = ColumnType::TYPE_TEXT)
```

| Constant | Value | Description |
|----------|-------|-------------|
| `ColumnType::TYPE_TEXT` | `'text'` | Plain text |
| `ColumnType::TYPE_INTEGER` | `'integer'` | Integer number |
| `ColumnType::TYPE_FLOAT` | `'float'` | Decimal / float number |
| `ColumnType::TYPE_SELECT` | `'select'` | Value from a fixed option list |
| `ColumnType::TYPE_DATE` | `'date'` | Formatted date |
| `ColumnType::TYPE_DATETIME` | `'datetime'` | Formatted datetime |
| `ColumnType::TYPE_COLOR` | `'color'` | Color display |

### Key ColumnType methods

| Method | Description |
|--------|-------------|
| `setOptions(OptionsInterface $options)` | Attach options list (required for `TYPE_SELECT`). Also enables translation if the options use `TRANSLATABLE_FILE`. |
| `setTranslate(bool $translate)` | Force translation on/off for the displayed value |
| `setTemplateField(string $template)` | Override the per-cell Twig template |

The default template for type `foo` is `@SpipuUi/grid/field/foo.html.twig`. Available field templates: `text`, `integer`, `float`, `select`, `select-color`, `date`, `datetime`, `color`, `yes-no`, `size`, `number`.

## Column Filters (`ColumnFilter`)

`ColumnFilter` controls whether and how a column can be filtered.

```php
new ColumnFilter(bool $filterable = false, bool $quickSearch = false)
```

| Argument | Description |
|----------|-------------|
| `$filterable` | Show this column in the filter bar |
| `$quickSearch` | Include this column in the quick-search (prefix LIKE) |

### Key ColumnFilter methods

| Method | Description |
|--------|-------------|
| `useRange(bool $range = true)` | Use a from/to range filter instead of a single value |
| `useExactValue(bool $exactValue = true)` | Use exact-match (`=`) instead of LIKE for text columns |
| `useMultipleValues(bool $multipleValues)` | Accept multiple values (renders `select-multiple` template) |
| `setTemplateFilter(string $template)` | Override the filter Twig template |
| `setValueTransformer(Closure $transformer)` | Set a closure `fn(string $value): string` to transform the filter value before query building (text/LIKE and quickSearch only) |

The default filter template is derived from the linked `ColumnType`: `@SpipuUi/grid/filter/<type>.html.twig`. For `useMultipleValues(true)` the template becomes `@SpipuUi/grid/filter/<type>-multiple.html.twig`. Available filter templates: `text`, `integer`, `float`, `select`, `select-multiple`, `date`, `datetime`, `color`.

The filter type behavior depends on the column's `ColumnType`:
- `TYPE_SELECT` columns always use exact-match equality (or `IN` for multiple values)
- Columns with `useRange()` use `>=` / `<=` comparisons
- Columns with `useExactValue()` use `=`
- All other text-like columns default to a LIKE `%value%` search

### Value Transformer

`setValueTransformer()` allows transforming the user input before it is used in the query. It applies only to text/LIKE filters and quickSearch — range, select, and exactValue filters are not affected.

```php
->addColumn(
    (new Column('email', 'user.field.email', 'email', 25))
        ->setType(new ColumnType(ColumnType::TYPE_TEXT))
        ->setFilter(
            (new ColumnFilter(true, true))
                ->setValueTransformer(fn(string $v): string => mb_strtolower($v))
        )
        ->useSortable()
)
```

The transformer is applied in the `DataProvider` via `applyValueTransformer(Column $column, ?string $value): ?string`, available on `AbstractDataProvider`. Custom data providers can call this method to benefit from the transformer.

## Actions (`Action`)

```php
new Action(string $code, string $name, int $position, string $routeName, array $routeParams = [])
```

| Argument | Description |
|----------|-------------|
| `$code` | Unique action key |
| `$name` | Translation key for button label |
| `$position` | Sort order |
| `$routeName` | Symfony route name |
| `$routeParams` | Static route parameters (merged with entity field values at render time) |

### Key Action methods

| Method | Description |
|--------|-------------|
| `setCssClass(?string $cssClass)` | Bootstrap button color class (e.g. `'primary'`, `'success'`, `'danger'`) |
| `setIcon(?string $icon)` | FontAwesome icon name (without `fa-` prefix) |
| `setNeededRole(?string $role)` | Symfony role required to see this action |
| `setConditions(array $conditions)` | Conditions on entity field values to show the action |
| `setDeniedIfPersonalDataHidden(bool $denied)` | Hide the action on rows whose personal data are hidden (see [Personal data protection](#personal-data-protection)) |
| `setBuildCallback(?callable $callback)` | Custom callable `(RouterInterface, Action, array $params, ?EntityInterface $row): string` to build the URL |

### Action conditions

Conditions are field-keyed arrays of operator-value pairs evaluated against each row object:

```php
->setConditions([
    'active' => ['eq' => 1],
    'id'     => ['neq' => $currentUserId],
])
```

Supported operators: `eq`, `neq`, `lt`, `gt`, `lte`, `gte`, `in`, `nin`, `callback`.

The `callback` operator receives the row entity object:

```php
->setConditions(['active' => ['callback' => fn($entity) => $entity->canBeDeleted()]])
```

## Using the Grid in a Controller

The `GridFactory` service creates a `GridManagerInterface` from your definition:

```php
use Spipu\UiBundle\Service\Ui\GridFactory;

class UserController extends AbstractController
{
    public function index(GridFactory $gridFactory, UserGrid $userGrid): Response
    {
        $manager = $gridFactory->create($userGrid);
        $manager->setRoute('app_user_index');

        if ($manager->validate()) {
            // validate() returns true if the grid config was changed and needs a page refresh
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/index.html.twig', [
            'manager' => $manager,
        ]);
    }
}
```

`setRoute()` must be called before `validate()`. It sets the Symfony route used to generate pagination and filter URLs.

In the Twig template, use the `renderManager` function:

```twig
{# templates/user/index.html.twig #}
{% extends 'base.html.twig' %}
{% block body %}
    {{ renderManager(manager) }}
{% endblock %}
```

## Customizing the Doctrine Data Provider

The default data provider (`Spipu\UiBundle\Service\Ui\Grid\DataProvider\Doctrine`) exposes three extension points:

```php
$manager->getDataProvider()->addCondition($expr);
$manager->getDataProvider()->addJoin('relationName', 'left');  // 'inner' or 'left'
$manager->getDataProvider()->addMappingValue('fieldCode', $originalValue, $newValue);
```

Call these after `$gridFactory->create()` but before `$manager->validate()`.

`forceFilters(array $filters)` can also be used to bypass the request-driven filter mechanism entirely.

## Personal data protection

Goal: limit the exposure of personal data, for example if the credentials of a back-office user are stolen.
Once a record no longer needs to be fully visible (a closed case, an order shipped months ago), its personal data are hidden in the UI.

- The data stay in the database: this is not an anonymization.
- The protection only applies to the UI components listed below (grids, show pages, Twig functions and macro).
- An API, an export or a custom template reading the entity directly is not protected.
- The controllers of the project are not protected: a mass action controller, or an edition not using `FormManager`, must check `PersonalDataProtection::isEntityHidden()` itself (a forged request can send the id of a hidden entity).

### Defining a rule

Each entity is responsible for its own fields only. A rule is a service implementing `Spipu\UiBundle\Service\PersonalData\PersonalDataRuleInterface`, tagged `spipu.ui.personal-data.rule`:

```php
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Service\PersonalData\PersonalDataRuleInterface;

class CustomerRule implements PersonalDataRuleInterface
{
    public function getEntityClass(): string
    {
        return Customer::class;
    }

    public function isActive(): bool
    {
        return !$this->security->isGranted('ROLE_ADMIN_FULL_VIEW');
    }

    public function getFields(): array
    {
        return ['email', 'lastName'];
    }

    public function isVisible(EntityInterface $entity): bool
    {
        return ($entity->getLastActivityAt() !== null) && ($entity->getLastActivityAt() >= $this->getLimitDate());
    }

    public function getVisibleDqlCondition(string $alias): array
    {
        return [$alias . '.lastActivityAt >= :limit', ['limit' => $this->getLimitDate()]];
    }
}
```

```yaml
services:
    App\Service\PersonalData\CustomerRule:
        autowire: true
        tags: ['spipu.ui.personal-data.rule']
```

| Method | Description |
|--------|-------------|
| `getEntityClass()` | Entity class of the rule. Only one rule per class (a duplicate throws a `UiException`). Child classes use the rule of their parent |
| `isActive()` | Whether the rule applies in the current context. An inactive rule is ignored: nothing hidden, no search condition, edition allowed. Example: `false` for an admin who keeps the full view |
| `getFields()` | Protected fields: own fields of the entity only, no dotted path |
| `isVisible(EntityInterface $entity)` | Whether the personal data of this entity are visible |
| `getVisibleDqlCondition(string $alias)` | DQL condition matching the visible rows on the given alias, and its parameters. The parameter names are free: the data provider renames them to avoid collisions |

The rule is a service: it can read a threshold from the configuration, the current date, the current user or firewall, etc. A threshold specific to each interface is read by the rule from the current context.

An entity without id (not saved yet) is never hidden, and its rule is not called. The active rule of each entity class is cached until the end of the request (`kernel.reset`): `isActive()` is called once per class and request. `isVisible()` is called once per entity and request: it must stay cheap.

`isVisible()` and `getVisibleDqlCondition()` express the same predicate, in PHP and in DQL: they must match exactly the same entities, `NULL` values included. In SQL, a comparison with `NULL` is never true: `a.x >= :limit` excludes the rows where `x` is `NULL`, so `isVisible()` must return `false` when `x` is `null`. Otherwise, the entity is fully displayed but never found by a search on a protected field. Cover both methods with the same test cases in the project.

Performance: a condition on an expression (`COALESCE(...)`, a function...) cannot use an index. Prefer an indexable form, for example `(a.x >= :limit OR (a.x IS NULL AND a.y >= :limit))` instead of `COALESCE(a.x, a.y) >= :limit`.

### Where the rule is applied

The rules are applied by the `Spipu\UiBundle\Service\PersonalData\PersonalDataProtection` service.

**Display: in the templates only.**

- `GridManager::getValue()` always returns the real value. The show page reads the value with `attribute(manager.resource, field.code)`.
- **Grid cells**: `grid/row.html.twig` checks `GridManager::isHiddenValue()` and displays the info badge "Hidden" (`@SpipuUi/grid/field/personal-data-hidden.html.twig`) instead of the column template.
- **Show pages**: `entity/viewFieldset.html.twig` checks `ShowManager::isHiddenValue()` and displays the same badge (`@SpipuUi/entity/view/personal-data-hidden.html.twig`) instead of the field view template.
- **Custom templates**: use the Twig functions `is_personal_data_hidden_entity(entity)` and `is_personal_data_hidden_field(entity, 'field')` (see [Form / View / Menu System](./form.md)). A custom template reading the entity without this check bypasses the protection.
- **Custom templates — display a value**: the macro `display` of `@SpipuUi/macro/personal_data.html.twig` displays the "Hidden" badge if the field is hidden, else the given value, already formatted:

```twig
{% import '@SpipuUi/macro/personal_data.html.twig' as personal_data %}
{{ personal_data.display(order, 'customerEmail', order.customerEmail) }}
{{ personal_data.display(order.customer, 'birthday', order.customer.birthday ? order.customer.birthday|date('Y-m-d') : '-') }}
```
- **Dotted paths** (`customer.email`): each segment is checked against the rule of the object owning it. The value of `customer.email` is hidden when the customer is hidden, whatever the state of the main entity.

**Search: in PHP.**

- **Doctrine filters and quick search**: a filter or a quick search on a protected field adds the visible condition of the rule, on the alias owning the field (`main` for a direct field, the join alias for a dotted field). For a dotted path, each protected segment adds the condition of its own rule (for example a protected `customer` association on the order, and a protected `email` on the customer). It is added once per alias. Range filters add it only when a bound is given.
- The class owning a dotted field is resolved with the Doctrine metadata (association target class). The join alias is the segment before the field, as with `addJoin()`.
- Only the Doctrine data provider applies the filter condition. A custom data provider must apply the rule itself, with `PersonalDataProtection::getVisibleDqlConditions()`: it returns the DQL condition and its parameters for each alias owning a protected segment of a path.
- **Sort**: the columns on a protected field stay sortable. The order of the rows may reveal a little information about the hidden values.

**Actions and edition: in PHP.**

- **Denied actions**: an action with `setDeniedIfPersonalDataHidden(true)` is not displayed on a hidden row (for example an edit action).
- **Mass actions**: the selection checkbox of a hidden row is disabled, with a tooltip, and "select all" ignores it: a hidden row can not be selected for a mass action. This is a UI protection only: the controller of a mass action receives ids, possibly forged, so it must check `PersonalDataProtection::isEntityHidden()` on the loaded entities if the action must not apply to hidden rows.
- **Action conditions** receive the real value.
- **Edition**: `FormManager::setResource()` throws an `AccessDeniedException` (HTTP 403) on a hidden entity: a hidden entity can not be edited through `FormManager`. Any other edition (Symfony form built directly, AJAX action, etc.) must check `PersonalDataProtection::isEntityHidden()` itself.

To check a dotted path, the intermediate objects are read with the Symfony PropertyAccess component (`symfony/property-access`). A `null` intermediate object means not hidden. An intermediate segment that cannot be read, or that is not an object, throws a `GridException`.

## Grid Personalization

Enabling `setPersonalize(true)` on the `Grid` allows users to save custom column selections. These are stored in the `spipu_ui_grid_config` database table via the `GridConfig` entity.

Users can create, select, and delete named configurations through the grid UI. The `GridIdentifierInterface` and `UserIdentifierInterface` services determine the per-user storage key.

Reset all saved configs with:

```bash
php bin/console spipu:ui:grid-config:reset
```

## Listening to Grid Definition Events

Every time a grid manager is created, it dispatches a `GridDefinitionEvent` with event code `spipu.ui.grid.definition.<grid_code>`. Subscribe to it to add columns, actions, or modify the definition dynamically:

```php
use Spipu\UiBundle\Event\GridDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyGridSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GridDefinitionEvent::PREFIX_NAME . 'user' => 'onGrid',
        ];
    }

    public function onGrid(GridDefinitionEvent $event): void
    {
        $grid = $event->getGridDefinition();

        $grid->setPersonalize(true);
        $grid->addColumn(
            (new Grid\Column('middle_name', 'user.field.middle_name', 'middleName', 35))
                ->setType(new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT))
                ->useSortable()
                ->setFilter(new Grid\ColumnFilter(true, false))
                ->setDisplayed(false)
        );
    }
}
```

[back](./README.md)
