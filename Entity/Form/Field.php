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
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\OptionsInterface;

class Field implements PositionInterface
{
    use PositionTrait;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isList = false;

    /**
     * @var bool
     */
    private $isHiddenInView = false;

    /**
     * @var bool
     */
    private $isHiddenInForm = false;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $templateView;

    /**
     * Fieldset constructor.
     * @param string $code
     * @param string $type
     * @param int $position
     * @param array $options
     * @throws FormException
     */
    public function __construct(string $code, string $type, int $position, array $options)
    {
        $this->code = $code;
        $this->type = $type;
        $this->options = [];
        $this->setPosition($position);
        $this->setTemplateView($this->getTemplateFromTypeClassname($type));

        foreach ($options as $key => $value) {
            $this->addOption($key, $value);
        }
    }

    /**
     * @param string $type
     * @return string
     */
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     * @throws FormException
     */
    public function addOption(string $key, $value): self
    {
        if ($key === 'choices') {
            if (!is_object($value) || !($value instanceof OptionsInterface)) {
                throw new FormException('The choices of field '.$this->code.' must implement OptionsInterface');
            }
        }

        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function getOption(string $code)
    {
        if (!array_key_exists($code, $this->options)) {
            return null;
        }

        return $this->options[$code];
    }

    /**
     * @return null|string
     */
    public function getLabel(): ?string
    {
        return $this->getOption('label');
    }

    /**
     * @return bool
     */
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

    /**
     * @return OptionsInterface|null
     */
    public function getChoices(): ?OptionsInterface
    {
        return $this->getOption('choices');
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

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
}
