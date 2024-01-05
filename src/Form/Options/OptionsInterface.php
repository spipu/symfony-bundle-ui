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
    public const TRANSLATABLE_NO = 'no';
    public const TRANSLATABLE_FILE = 'file';

    public function resetOptions(): bool;

    /**
     * @return string[]
     */
    public function getOptions(): array;

    /**
     * @return string[]
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

    public function hasKey(mixed $key): bool;

    public function getValueFromKey(mixed $key): ?string;

    public function getTranslatableType(): string;
}
