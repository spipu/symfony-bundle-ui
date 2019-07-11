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

namespace Spipu\UiBundle\Form\Options;

abstract class AbstractOptions implements OptionsInterface
{
    /**
     * @var string[]
     */
    private $options;

    /**
     * Build the list of the available options
     * @return array
     */
    abstract protected function buildOptions(): array;

    /**
     * List of available options
     * @return string[]
     */
    public function getOptions(): array
    {
        if (!is_array($this->options)) {
            $this->options = $this->buildOptions();
        }

        return $this->options;
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

    /**
     * @param mixed $key
     * @return bool
     */
    public function hasKey($key): bool
    {
        if (is_bool($key)) {
            $key = (int) $key;
        }

        return array_key_exists($key, $this->getOptions());
    }
}
