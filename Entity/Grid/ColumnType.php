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

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $templateField;

    /**
     * @var OptionsInterface|null
     */
    private $options = null;

    /**
     * @var bool
     */
    private $translate = false;

    /**
     * Type constructor.
     * @param string $type
     * @throws GridException
     */
    public function __construct(
        string $type = self::TYPE_TEXT
    ) {
        $this->setType($type);
    }

    /**
     * @param string $type
     * @return void
     * @throws GridException
     */
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

    /**
     * @return OptionsInterface|null
     */
    public function getOptions(): ?OptionsInterface
    {
        return $this->options;
    }

    /**
     * @param OptionsInterface $options
     * @return self
     */
    public function setOptions(OptionsInterface $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTranslate(): bool
    {
        return $this->translate;
    }

    /**
     * @param bool $translate
     * @return self
     */
    public function setTranslate(bool $translate): self
    {
        $this->translate = $translate;

        return $this;
    }


    /**
     * @return string
     */
    public function getTemplateField(): string
    {
        return $this->templateField;
    }

    /**
     * @param string $templateField
     * @return self
     */
    public function setTemplateField(string $templateField): self
    {
        $this->templateField = $templateField;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     * @throws GridException
     */
    public function setType(string $type): self
    {
        $this->validateType($type);

        $this->type = $type;
        $this->templateField = '@SpipuUi/grid/field/' . $type . '.html.twig';
        $this->translate = ($type === 'select');

        return $this;
    }
}
