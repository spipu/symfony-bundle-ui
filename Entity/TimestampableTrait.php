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

#[ORM\HasLifecycleCallbacks()]
trait TimestampableTrait
{
    #[ORM\Column(type: "datetime", nullable: false)]
    protected ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: false)]
    protected ?DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist()]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }

    #[ORM\PrePersist()]
    #[ORM\PreUpdate()]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
