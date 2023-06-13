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
use Spipu\UiBundle\Entity\PositionTrait;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\OptionsInterface;

class Field implements PositionInterface
{
    use PositionTrait;

    private string $code;
    private string $type;
    private bool $isList = false;
    private bool $isHiddenInView = false;
    private bool $isHiddenInForm = false;
    private array $options = [];
    private mixed $value = null;
    private string $templateView = '';

    /**
     * @var FieldConstraint[]
     */
    private array $constraints = [];

    public function __construct(string $code, string $type, int $position, array $options)
    {
        $this->code = $code;
        $this->type = $type;
        $this->setPosition($position);
        $this->setTemplateView($this->getTemplateFromTypeClassname($type));

        foreach ($options as $key => $value) {
            $this->addOption($key, $value);
        }
    }

    private function getTemplateFromTypeClassname(string $type): string
    {
        $type = str_replace('\\', '/', $type);
        $type = basename($type);
        $type = preg_replace('/Type$/', '', $type);
        $type = strtolower($type);

        switch ($type) {
            case 'date':
                $template = '@SpipuUi/entity/view/date.html.twig';
                break;

            case 'datetime':
                $template = '@SpipuUi/entity/view/datetime.html.twig';
                break;

            case 'choice':
                $template = '@SpipuUi/entity/view/select.html.twig';
                break;

            case 'color':
                $template = '@SpipuUi/entity/view/color.html.twig';
                break;

            case 'entity':
                $template = '@SpipuUi/entity/view/entity.html.twig';
                break;

            case 'password':
                $template = '@SpipuUi/entity/view/password.html.twig';
                break;

            default:
                $template = '@SpipuUi/entity/view/text.html.twig';
                break;
        }

        return $template;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function addOption(string $key, mixed $value): self
    {
        if ($key === 'choices') {
            if (!is_object($value) || !($value instanceof OptionsInterface)) {
                throw new FormException('The choices of field ' . $this->code . ' must implement OptionsInterface');
            }
        }

        $this->options[$key] = $value;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $code): mixed
    {
        if (!array_key_exists($code, $this->options)) {
            return null;
        }

        return $this->options[$code];
    }

    public function getLabel(): ?string
    {
        return $this->getOption('label');
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    /**
     * @param bool $isList
     * @return self
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function useList(bool $isList = true): self
    {
        $this->isList = $isList;

        return $this;
    }

    public function getChoices(): ?OptionsInterface
    {
        return $this->getOption('choices');
    }

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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

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

    public function addConstraint(FieldConstraint $fieldConstraint): self
    {
        $this->constraints[$fieldConstraint->getCode()] = $fieldConstraint;

        return $this;
    }

    public function getConstraint(string $code): ?FieldConstraint
    {
        if (!array_key_exists($code, $this->constraints)) {
            return null;
        }

        return $this->constraints[$code];
    }

    public function deleteConstraint(string $code): self
    {
        if (array_key_exists($code, $this->constraints)) {
            unset($this->constraints[$code]);
        }

        return $this;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function resetConstraints(): self
    {
        $this->constraints = [];

        return $this;
    }

    public function getConstraintsAsArray(): array
    {
        $values = [];
        foreach ($this->constraints as $constraint) {
            $values[] = ['field' => $constraint->getFieldCode(), 'value' => $constraint->getFieldValue()];
        }

        return $values;
    }
}
