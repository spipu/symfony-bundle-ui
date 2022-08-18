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

namespace Spipu\UiBundle\Entity;

interface PositionInterface
{
    /**
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void;

    /**
     * @return int
     */
    public function getPosition(): int;
}
