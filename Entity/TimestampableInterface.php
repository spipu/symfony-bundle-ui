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

use DateTimeInterface;

/**
 * You must use this interface with the corresponding trait TimestampableTrait
 */
interface TimestampableInterface
{
     /**
     * Set the created at value on create
     * @return void
     */
    public function setCreatedAtValue(): void;

    /**
     * Set the updated at value on update
     * @return void
     */
    public function setUpdatedAtValue(): void;

    /**
     * Get - Created At
     *
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface;

    /**
     * Get - Updated At
     *
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface;
}
