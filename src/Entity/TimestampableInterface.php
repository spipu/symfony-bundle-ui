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

use DateTimeInterface;

/**
 * You must use this interface with the corresponding trait TimestampableTrait
 */
interface TimestampableInterface
{
    public function setCreatedAtValue(): void;

    public function setUpdatedAtValue(): void;

    public function getCreatedAt(): ?DateTimeInterface;

    public function getUpdatedAt(): ?DateTimeInterface;
}
