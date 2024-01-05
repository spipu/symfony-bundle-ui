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

namespace Spipu\UiBundle\Form\Options;

abstract class AbstractOptions implements OptionsInterface
{
    /**
     * @var string[]|null
     */
    private ?array $options = null;

    abstract protected function buildOptions(): array;

    private function loadOptions(): void
    {
        if (!is_array($this->options)) {
            $this->options = $this->buildOptions();
        }
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        $this->loadOptions();

        return $this->options;
    }

    public function resetOptions(): bool
    {
        $this->options = null;

        return true;
    }

    /**
     * @return string[]
     */
    public function getOptionsWithEmptyValue(): array
    {
        return ['' => ' '] + $this->getOptions();
    }

    /**
     * @return string[]
     */
    public function getOptionsInverse(): array
    {
        return array_flip($this->getOptions());
    }

    /**
     * @return string[]
     */
    public function getOptionsWithEmptyValueInverse(): array
    {
        return [' ' => ''] + $this->getOptionsInverse();
    }

    public function hasKey(mixed $key): bool
    {
        $this->loadOptions();

        if (is_bool($key)) {
            $key = (int) $key;
        }

        return array_key_exists($key, $this->options);
    }

    public function getValueFromKey(mixed $key): ?string
    {
        $this->loadOptions();

        if (is_bool($key)) {
            $key = (int) $key;
        }

        return array_key_exists($key, $this->options) ? $this->options[$key] : null;
    }

    public function getTranslatableType(): string
    {
        return self::TRANSLATABLE_FILE;
    }
}
