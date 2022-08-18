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

interface OptionsInterface
{
    /**
     * List of available options
     * @return string[]
     */
    public function getOptions(): array;

    /**
     * @return bool
     */
    public function resetOptions(): bool;

    /**
     * @return array
     */
    public function getOptionsWithEmptyValue(): array;

    /**
     * @return string[]
     */
    public function getOptionsInverse(): array;

    /**
     * @return string[]
     */
    public function getOptionsWithEmptyValueInverse(): array;

    /**
     * @param mixed $key
     * @return bool
     */
    public function hasKey($key): bool;

    /**
     * @param mixed $key
     * @return string|null
     */
    public function getValueFromKey($key): ?string;
}
