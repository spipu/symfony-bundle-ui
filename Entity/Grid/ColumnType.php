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

namespace Spipu\UiBundle\Entity\Grid;

use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Form\Options\OptionsInterface;

class ColumnType
{
    public const TYPE_TEXT     = 'text';
    public const TYPE_INTEGER  = 'integer';
    public const TYPE_FLOAT    = 'float';
    public const TYPE_SELECT   = 'select';
    public const TYPE_DATE     = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_COLOR    = 'color';

    private string $type;
    private string $templateField;
    private ?OptionsInterface $options = null;
    private bool $translate = false;

    public function __construct(
        string $type = self::TYPE_TEXT
    ) {
        $this->setType($type);
    }

    private function validateType(string $type): void
    {
        $allowedTypes = [
            self::TYPE_TEXT,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_SELECT,
            self::TYPE_DATE,
            self::TYPE_DATETIME,
            self::TYPE_COLOR,
        ];

        if (!in_array($type, $allowedTypes)) {
            throw new GridException('Invalid Column Type');
        }
    }

    public function getOptions(): ?OptionsInterface
    {
        return $this->options;
    }

    public function setOptions(OptionsInterface $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function isTranslate(): bool
    {
        return $this->translate;
    }

    public function setTranslate(bool $translate): self
    {
        $this->translate = $translate;

        return $this;
    }

    public function getTemplateField(): string
    {
        return $this->templateField;
    }

    public function setTemplateField(string $templateField): self
    {
        $this->templateField = $templateField;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->validateType($type);

        $this->type = $type;
        $this->templateField = '@SpipuUi/grid/field/' . $type . '.html.twig';
        $this->translate = ($type === 'select');

        return $this;
    }
}
