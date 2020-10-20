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

class Form
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string|null
     */
    private $entityClassName;

    /**
     * @var FieldSet[]
     */
    private $fieldSets = [];

    /**
     * @var string
     */
    private $templateForm = '@SpipuUi/entity/form.html.twig';

    /**
     * @var string
     */
    private $templateView = '@SpipuUi/entity/view.html.twig';

    /**
     * @var string
     */
    private $validateSuccessMessage = 'spipu.ui.success.saved';

    /**
     * Form constructor.
     * @param string $code
     * @param string|null $entityClassName
     */
    public function __construct(string $code, string $entityClassName = null)
    {
        $this->code = $code;
        $this->entityClassName = $entityClassName;
    }

    /**
     * @param FieldSet $fieldSet
     * @return self
     */
    public function addFieldSet(FieldSet $fieldSet): self
    {
        $this->fieldSets[$fieldSet->getCode()] = $fieldSet;

        return $this;
    }

    /**
     * @param string $key
     * @return Form
     */
    public function removeFieldSet(string $key): self
    {
        if (array_key_exists($key, $this->fieldSets)) {
            unset($this->fieldSets[$key]);
        }

        return $this;
    }

    /**
     * @return FieldSet[]
     */
    public function getFieldSets(): array
    {
        return $this->fieldSets;
    }

    /**
     * @param string $key
     * @return FieldSet|null
     */
    public function getFieldSet(string $key): ?FieldSet
    {
        if (!array_key_exists($key, $this->fieldSets)) {
            return null;
        }

        return $this->fieldSets[$key];
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    /**
     * @return string
     */
    public function getTemplateForm(): string
    {
        return $this->templateForm;
    }

    /**
     * @param string $templateForm
     * @return self
     */
    public function setTemplateForm(string $templateForm): self
    {
        $this->templateForm = $templateForm;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateView(): string
    {
        return $this->templateView;
    }

    /**
     * @param string $templateView
     * @return self
     */
    public function setTemplateView(string $templateView): self
    {
        $this->templateView = $templateView;

        return $this;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param string|null $entityClassName
     * @return self
     */
    public function setEntityClassName(?string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

    /**
     * Sort the fieldSets
     *
     * @return void
     */
    public function prepareSort(): void
    {
        uasort(
            $this->fieldSets,
            function (PositionInterface $rowA, PositionInterface $rowB) {
                return ($rowA->getPosition() <=> $rowB->getPosition());
            }
        );

        foreach ($this->fieldSets as $fieldSet) {
            $fieldSet->prepareSort();
        }
    }

    /**
     * @return string
     */
    public function getValidateSuccessMessage(): string
    {
        return $this->validateSuccessMessage;
    }

    /**
     * @param string $validateSuccessMessage
     * @return $this
     */
    public function setValidateSuccessMessage(string $validateSuccessMessage): self
    {
        $this->validateSuccessMessage = $validateSuccessMessage;

        return $this;
    }
}
