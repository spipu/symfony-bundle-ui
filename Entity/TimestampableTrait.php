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

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * You must add the following comment on all the entities:
 * @ORM\HasLifecycleCallbacks()
 */
trait TimestampableTrait
{
    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * Set the created at value on create
     * @ORM\PrePersist()
     * @return void
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * Set the updated at value on update
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @return void
     */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * Get - Created At
     *
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Get - Updated At
     *
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
