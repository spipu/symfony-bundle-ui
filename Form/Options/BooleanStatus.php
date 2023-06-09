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

class BooleanStatus extends AbstractOptions
{
    public const VALUE_TRUE = 1;
    public const VALUE_FALSE = 0;

    protected function buildOptions(): array
    {
        return [
            self::VALUE_TRUE  => 'spipu.ui.options.value_true',
            self::VALUE_FALSE => 'spipu.ui.options.value_false',
        ];
    }
}
