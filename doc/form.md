# Form System

[back](./README.md)

## Overview

UiBundle provides a form management layer on top of Symfony Forms. A form is defined by implementing `EntityDefinitionInterface`, which builds a `Form` entity describing fieldsets and fields. The `FormFactory` and `ShowFactory` services process and render the form (edit) or view (read-only) respectively.

## Core Interfaces

### `EntityDefinitionInterface`

```php
namespace Spipu\UiBundle\Service\Ui\Definition;

interface EntityDefinitionInterface
{
    public function getDefinition(): Form;

    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void;
}
```

- `getDefinition()` — builds and returns the `Form` description object
- `setSpecificFields()` — called after a successful form submission; use it to set computed or derived fields that are not directly mapped (e.g. encoded passwords)

## Defining a Form

Build a `Form` containing one or more `FieldSet` objects, each containing one or more `Field` objects:

```php
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;

class UserFormDefinition implements EntityDefinitionInterface
{
    public function getDefinition(): Form
    {
        return (new Form('user_admin', App\Entity\User::class))
            ->addFieldSet(
                (new FieldSet('information', 'spipu.user.fieldset.information', 10))
                    ->addField(new Field('username', TextType::class, 10, [
                        'label'    => 'spipu.user.field.username',
                        'required' => true,
                        'trim'     => true,
                    ]))
                    ->addField(new Field('email', EmailType::class, 20, [
                        'label'    => 'spipu.user.field.email',
                        'required' => true,
                    ]))
            )
            ->addFieldSet(
                (new FieldSet('password', 'spipu.user.fieldset.password', 20))
                    ->useHiddenInView(true)
                    ->addField(
                        (new Field('plainPassword', PasswordType::class, 10, [
                            'label'    => 'spipu.user.field.password',
                            'required' => false,
                            'mapped'   => false,
                        ]))->useHiddenInView(true)
                    )
            )
        ;
    }

    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void
    {
        // Example: encode a plain password that was submitted
        $plainPassword = $form->get('plainPassword')->getData();
        if ($plainPassword && $resource instanceof App\Entity\User) {
            $resource->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));
        }
    }
}
```

## Form Entity

```php
new Form(string $code, ?string $entityClassName = null)
```

| Argument | Description |
|----------|-------------|
| `$code` | Unique form identifier (used for event names and CSRF token IDs) |
| `$entityClassName` | FQCN of the Doctrine entity this form edits; `null` for forms without an entity |

### Key Form methods

| Method | Description |
|--------|-------------|
| `addFieldSet(FieldSet $fieldSet)` | Add a fieldset |
| `removeFieldSet(string $key)` | Remove a fieldset by code |
| `getFieldSet(string $key)` | Retrieve a fieldset by code |
| `setTemplateForm(string $template)` | Override the form template (default: `@SpipuUi/entity/form.html.twig`) |
| `setTemplateView(string $template)` | Override the read-only view template (default: `@SpipuUi/entity/view.html.twig`) |
| `setValidateSuccessMessage(string $message)` | Translation key for the flash message on success (default: `spipu.ui.success.saved`) |
| `setEntityClassName(?string $className)` | Set or change the entity class at runtime |

## FieldSet Entity

```php
new FieldSet(string $code, string $name, int $position)
```

| Argument | Description |
|----------|-------------|
| `$code` | Unique key within the form (used to retrieve it via `$form->getFieldSet('code')`) |
| `$name` | Translation key for the fieldset heading |
| `$position` | Sort order |

### Key FieldSet methods

| Method | Description |
|--------|-------------|
| `addField(Field $field)` | Add a field |
| `removeField(string $key)` | Remove a field by code |
| `getField(string $key)` | Retrieve a field by code |
| `useHiddenInForm(bool $hidden = true)` | Hide this entire fieldset in the form (edit) view |
| `useHiddenInView(bool $hidden = true)` | Hide this entire fieldset in the read-only view |
| `setCssClass(string $cssClass)` | Bootstrap column class applied to the fieldset wrapper (default: `'col-12'`) |

## Field Entity

```php
new Field(string $code, string $type, int $position, array $options)
```

| Argument | Description |
|----------|-------------|
| `$code` | Field name — must match the entity property name when using `data_class` |
| `$type` | Symfony form type FQCN (e.g. `TextType::class`, `ChoiceType::class`) |
| `$position` | Sort order within the fieldset |
| `$options` | Symfony form field options array (e.g. `['label' => '...', 'required' => true]`) |

When using the `choices` option, the value **must** be an instance of `OptionsInterface` — plain arrays are not accepted:

```php
new Field('status', ChoiceType::class, 30, [
    'label'   => 'field.status',
    'choices' => $this->activeStatusOptions,   // implements OptionsInterface
    'required' => false,
])
```

### Key Field methods

| Method | Description |
|--------|-------------|
| `useHiddenInForm(bool $hidden = true)` | Hide this field from the form (edit) view |
| `useHiddenInView(bool $hidden = true)` | Hide this field from the read-only view |
| `useList(bool $isList = true)` | Mark this field for list display (unused by default templates, available for custom logic) |
| `setValue(mixed $value)` | Set a default value for the field (used when no entity resource is provided) |
| `setTemplateView(string $template)` | Override the read-only view template for this field |
| `addConstraint(FieldConstraint $constraint)` | Add a display constraint linking this field to another |
| `addOption(string $key, mixed $value)` | Add or override a Symfony form option |

### View templates per field type

The read-only view template is automatically determined from the Symfony type class name:

| Type class | Template |
|------------|----------|
| `DateType` | `@SpipuUi/entity/view/date.html.twig` |
| `DateTimeType` | `@SpipuUi/entity/view/datetime.html.twig` |
| `ChoiceType` | `@SpipuUi/entity/view/select.html.twig` |
| `ColorType` | `@SpipuUi/entity/view/color.html.twig` |
| `EntityType` | `@SpipuUi/entity/view/entity.html.twig` |
| `PasswordType` | `@SpipuUi/entity/view/password.html.twig` |
| anything else | `@SpipuUi/entity/view/text.html.twig` |

Override with `setTemplateView()` on the field.

## FieldConstraint

A `FieldConstraint` expresses a dependency condition between fields for display purposes:

```php
use Spipu\UiBundle\Entity\Form\FieldConstraint;

new FieldConstraint(string $code, string $fieldCode, string $fieldValue)
```

This is used by custom templates to conditionally show/hide fields when another field has a specific value.

## Custom Form Types

The bundle provides two custom Symfony form types:

- `Spipu\UiBundle\Form\Type\IntegerUnitType` — extends `IntegerType` with a `unit` option (string, default `'Kg'`), block prefix `integerunit`
- `Spipu\UiBundle\Form\Type\NumberUnitType` — extends `NumberType` with a `unit` option (string, default `'Kg'`), block prefix `numberunit`

These render an input with a unit suffix label.

## Using the Form in a Controller

The `FormFactory` service creates a `FormManagerInterface` from your definition:

```php
use Spipu\UiBundle\Service\Ui\FormFactory;

class UserController extends AbstractController
{
    public function edit(
        int $id,
        FormFactory $formFactory,
        UserFormDefinition $formDefinition,
        UserRepository $repository
    ): Response {
        $user = $repository->find($id);

        $manager = $formFactory->create($formDefinition);
        $manager->setResource($user);
        $manager->setSubmitButton('spipu.ui.action.save', 'save');

        if ($manager->validate()) {
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit.html.twig', [
            'manager' => $manager,
        ]);
    }
}
```

Key `FormManagerInterface` methods:

| Method | Description |
|--------|-------------|
| `setResource(EntityInterface $resource)` | Set the entity being edited |
| `setSubmitButton(string $label, string $icon = 'edit')` | Override the submit button label (translation key) and icon |
| `validate(): bool` | Handle the request; returns `true` on successful save |
| `display(): string` | Render the form HTML |
| `getForm(): FormInterface` | Access the underlying Symfony `FormInterface` |
| `getDefinition(): Form` | Access the `Form` definition |

`validate()` automatically:
1. Calls `setSpecificFields()` on the definition
2. Dispatches a `FormSaveEvent` (`spipu.ui.form.save.<form_code>`)
3. Persists and flushes the entity via Doctrine
4. Adds a success flash message

In the Twig template, use the `renderManager` function:

```twig
{# templates/user/edit.html.twig #}
{% extends 'base.html.twig' %}
{% block body %}
    {{ renderManager(manager) }}
{% endblock %}
```

## Displaying a Read-Only View

Use `ShowFactory` to display an entity without an edit form:

```php
use Spipu\UiBundle\Service\Ui\ShowFactory;

class UserController extends AbstractController
{
    public function show(
        int $id,
        ShowFactory $showFactory,
        UserFormDefinition $formDefinition,
        UserRepository $repository
    ): Response {
        $user = $repository->find($id);

        $manager = $showFactory->create($formDefinition);
        $manager->setResource($user);
        $manager->validate();

        return $this->render('user/show.html.twig', [
            'manager' => $manager,
        ]);
    }
}
```

```twig
{# templates/user/show.html.twig #}
{% extends 'base.html.twig' %}
{% block body %}
    {{ renderManager(manager) }}
{% endblock %}
```

`ShowManager` uses the same `EntityDefinitionInterface` definition as `FormManager`. It renders `$form->getTemplateView()` (default: `@SpipuUi/entity/view.html.twig`) and respects `useHiddenInView()` on fieldsets and fields.

## Listening to Form Events

### `FormDefinitionEvent`

Dispatched when a form or show manager prepares the definition. Event code: `spipu.ui.form.definition.<form_code>`.

Use this to modify the definition dynamically (add fields, change the entity class, etc.):

```php
use Spipu\UiBundle\Event\FormDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserFormSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormDefinitionEvent::PREFIX_NAME . 'user_admin' => 'onUser',
        ];
    }

    public function onUser(FormDefinitionEvent $event): void
    {
        $event->getFormDefinition()->setEntityClassName(App\Entity\User::class);

        $event->getFormDefinition()->getFieldSet('information')->addField(
            new Field('middlename', TextType::class, 15, [
                'label'    => 'user.field.middle_name',
                'required' => false,
                'trim'     => true,
            ])
        );
    }
}
```

### `FormSaveEvent`

Dispatched just before the entity is persisted (inside a successful `validate()` call). Event code: `spipu.ui.form.save.<form_code>`.

```php
use Spipu\UiBundle\Event\FormSaveEvent;

class MyFormSaveSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormSaveEvent::PREFIX_NAME . 'user_admin' => 'onSave',
        ];
    }

    public function onSave(FormSaveEvent $event): void
    {
        $form = $event->getForm();
        $resource = $event->getResource();  // the EntityInterface being saved (may be null)
        $definition = $event->getFormDefinition();
    }
}
```

## Options System (`OptionsInterface`)

Options provide a controlled list of key-label pairs for `ChoiceType` fields and `TYPE_SELECT` grid columns.

### Built-in options classes

| Class | Values |
|-------|--------|
| `Spipu\UiBundle\Form\Options\YesNo` | `0 => 'No'`, `1 => 'Yes'` |
| `Spipu\UiBundle\Form\Options\ActiveStatus` | `0 => 'Disabled'`, `1 => 'Enabled'` |
| `Spipu\UiBundle\Form\Options\BooleanStatus` | `0 => 'False'`, `1 => 'True'` |

All three are registered as public services in the container.

### Creating custom options

Extend `AbstractOptions` and implement `buildOptions()`:

```php
use Spipu\UiBundle\Form\Options\AbstractOptions;

class StatusOptions extends AbstractOptions
{
    protected function buildOptions(): array
    {
        return [
            'draft'     => 'status.draft',
            'published' => 'status.published',
            'archived'  => 'status.archived',
        ];
    }
}
```

`AbstractOptions` caches the result of `buildOptions()` after the first call. Call `resetOptions()` to clear the cache.

`getTranslatableType()` returns `OptionsInterface::TRANSLATABLE_FILE` by default, meaning the values are used as translation keys. Return `OptionsInterface::TRANSLATABLE_NO` to use them as literal labels.

### Twig filters for options

Two Twig filters are available (provided by `OptionsExtension`):

| Filter | Signature | Description |
|--------|-----------|-------------|
| `label_from_option` | `value\|label_from_option(optionsObject)` | Resolve a value to its label using an `OptionsInterface` instance |
| `label_from_option_name` | `value\|label_from_option_name('App\\Options\\MyOptions')` | Resolve using the options service class name (fetched from DI container) |

## Twig Functions and Filters

### Functions (provided by `UiExtension`)

| Function | Signature | Description |
|----------|-----------|-------------|
| `renderManager` | `renderManager(UiManagerInterface $manager)` | Renders a grid, form, or show manager by calling `$manager->display()` |
| `getMenu` | `getMenu(string $currentItemCode): Item` | Builds and returns the menu tree with active/allowed state resolved |
| `getTranslations` | `getTranslations(array $codes): array` | Translates an array of translation key codes, returning a `key => translated` map |
| `isClosure` | `isClosure(mixed $var): bool` | Returns `true` if the variable is a `\Closure` |
| `executeClosure` | `executeClosure(callable $closure, array $variables = []): mixed` | Calls a closure with the given arguments |

### Filters (provided by `OptionsExtension`)

| Filter | Description |
|--------|-------------|
| `label_from_option` | Resolve a value to its label using an `OptionsInterface` instance |
| `label_from_option_name` | Resolve a value to its label using an options class name string |

## Entity Traits and Interfaces

### `EntityInterface`

```php
interface EntityInterface
{
    public function getId(): ?int;
}
```

Implement this on any Doctrine entity used with `FormManager` or `ShowManager`.

### `TimestampableInterface` and `TimestampableTrait`

Add automatic `createdAt` / `updatedAt` Doctrine lifecycle timestamps to an entity:

```php
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\TimestampableInterface;
use Spipu\UiBundle\Entity\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MyEntity implements EntityInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int { return $this->id; }
}
```

The trait defines:
- `$createdAt` — set once by `@PrePersist`
- `$updatedAt` — set by `@PrePersist` and `@PreUpdate`
- `getCreatedAt(): ?DateTimeInterface`
- `getUpdatedAt(): ?DateTimeInterface`

[back](./README.md)
