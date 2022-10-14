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

namespace Spipu\UiBundle\Service\Ui\Grid;

use Spipu\UiBundle\Entity\Grid\Grid;

interface GridIdentifierInterface
{
    /**
     * @param Grid $grid
     * @return string
     */
    public function getIdentifier(Grid $grid): string;
}
