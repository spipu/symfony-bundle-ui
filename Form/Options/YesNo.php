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

class YesNo extends AbstractOptions
{
    public const VALUE_YES = 1;
    public const VALUE_NO = 0;

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        return [
            self::VALUE_YES => 'spipu.ui.options.value_yes',
            self::VALUE_NO => 'spipu.ui.options.value_no',
        ];
    }
}
