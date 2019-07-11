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

namespace Spipu\UiBundle\Entity\Form;

use Spipu\UiBundle\Entity\PositionInterface;
use Spipu\UiBundle\Entity\PositionTrait;

class FieldSet implements PositionInterface
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
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var bool
     */
    private $isHiddenInView = false;

    /**
     * @var bool
     */
    private $isHiddenInForm = false;

    /**
     * @var string
     */
    private $cssClass = 'col-12';

    /**
     * Fieldset constructor.
     * @param string $code
     * @param string $name
     * @param int $position
     */
    public function __construct(string $code, string $name, int $position)
    {
        $this->code = $code;
        $this->name = $name;

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
     * @return bool
     */
    public function isHiddenInView(): bool
    {
        return $this->isHiddenInView;
    }

    /**
     * @param bool $isHiddenInView
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useHiddenInView(bool $isHiddenInView = true): self
    {
        $this->isHiddenInView = $isHiddenInView;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHiddenInForm(): bool
    {
        return $this->isHiddenInForm;
    }

    /**
     * @param bool $isHiddenInForm
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useHiddenInForm(bool $isHiddenInForm = true): self
    {
        $this->isHiddenInForm = $isHiddenInForm;

        return $this;
    }

    /**
     * @return string
     */
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    /**
     * @param string $cssClass
     * @return self
     */
    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * @param Field $field
     * @return self
     */
    public function addField(Field $field): self
    {
        $this->fields[$field->getCode()] = $field;

        return $this;
    }

    /**
     * @param string $key
     * @return FieldSet
     */
    public function removeField(string $key): self
    {
        if (array_key_exists($key, $this->fields)) {
            unset($this->fields[$key]);
        }

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string $key
     * @return Field|null
     */
    public function getField(string $key): ?Field
    {
        if (!array_key_exists($key, $this->fields)) {
            return null;
        }

        return $this->fields[$key];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sort the fields
     *
     * @return void
     */
    public function prepareSort(): void
    {
        uasort(
            $this->fields,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );
    }
}
