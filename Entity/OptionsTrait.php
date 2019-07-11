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

namespace Spipu\UiBundle\Entity;

trait OptionsTrait
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function getOption(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->options)) {
            return $default;
        }

        return $this->options[$key];
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * MUST RETURN VOID
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }
}
