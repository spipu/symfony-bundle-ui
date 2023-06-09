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

class ActiveStatus extends AbstractOptions
{
    public const VALUE_ACTIVE = 1;
    public const VALUE_INACTIVE = 0;

    protected function buildOptions(): array
    {
        return [
            self::VALUE_ACTIVE => 'spipu.ui.options.value_enabled',
            self::VALUE_INACTIVE => 'spipu.ui.options.value_disabled',
        ];
    }
}
