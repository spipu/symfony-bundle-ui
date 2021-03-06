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

class BooleanStatus extends AbstractOptions
{
    const VALUE_TRUE = 1;
    const VALUE_FALSE = 0;

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        return [
            self::VALUE_TRUE  => 'spipu.ui.options.value_true',
            self::VALUE_FALSE => 'spipu.ui.options.value_false',
        ];
    }
}
