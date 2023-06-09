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

namespace Spipu\UiBundle\Entity\Form;

use Spipu\UiBundle\Entity\PositionInterface;

class Form
{
    private string $code;
    private ?string $entityClassName;

    /**
     * @var FieldSet[]
     */
    private array $fieldSets = [];

    private string $templateForm = '@SpipuUi/entity/form.html.twig';
    private string $templateView = '@SpipuUi/entity/view.html.twig';
    private string $validateSuccessMessage = 'spipu.ui.success.saved';

    public function __construct(string $code, string $entityClassName = null)
    {
        $this->code = $code;
        $this->entityClassName = $entityClassName;
    }

    public function addFieldSet(FieldSet $fieldSet): self
    {
        $this->fieldSets[$fieldSet->getCode()] = $fieldSet;

        return $this;
    }

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

    public function getFieldSet(string $key): ?FieldSet
    {
        if (!array_key_exists($key, $this->fieldSets)) {
            return null;
        }

        return $this->fieldSets[$key];
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getEntityClassName(): ?string
    {
        return $this->entityClassName;
    }

    public function getTemplateForm(): string
    {
        return $this->templateForm;
    }

    public function setTemplateForm(string $templateForm): self
    {
        $this->templateForm = $templateForm;
        return $this;
    }

    public function getTemplateView(): string
    {
        return $this->templateView;
    }

    public function setTemplateView(string $templateView): self
    {
        $this->templateView = $templateView;

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setEntityClassName(?string $entityClassName): self
    {
        $this->entityClassName = $entityClassName;

        return $this;
    }

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

    public function getValidateSuccessMessage(): string
    {
        return $this->validateSuccessMessage;
    }

    public function setValidateSuccessMessage(string $validateSuccessMessage): self
    {
        $this->validateSuccessMessage = $validateSuccessMessage;

        return $this;
    }
}
