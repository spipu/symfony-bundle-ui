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

namespace Spipu\UiBundle\Entity\Grid;

class Pager
{
    /**
     * @var int[]
     */
    private $lengths;

    /**
     * @var int
     */
    private $defaultLength;

    /**
     * Pager constructor.
     * @param int[] $lengths
     * @param int $defaultLength
     */
    public function __construct(
        array $lengths = [10, 20, 50, 100],
        int $defaultLength = 20
    ) {
        $this->setLengths($lengths);
        $this->setDefaultLength($defaultLength);
    }

    /**
     * @return int[]
     */
    public function getLengths(): array
    {
        return $this->lengths;
    }

    /**
     * @return int
     */
    public function getDefaultLength(): int
    {
        return $this->defaultLength;
    }

    /**
     * @param int[] $lengths
     * @return self
     */
    public function setLengths(array $lengths): self
    {
        foreach ($lengths as $key => $value) {
            $value = (int) $value;
            if ($value < 1) {
                $value = 1;
            }

            $lengths[$key] = $value;
        }
        $lengths = array_unique($lengths);
        sort($lengths);

        if (count($lengths) < 1) {
            $lengths = [20];
        }

        $this->lengths = $lengths;
        $this->defaultLength = $lengths[0];

        return $this;
    }

    /**
     * @param int $defaultLength
     * @return self
     */
    public function setDefaultLength(int $defaultLength): self
    {
        if (!in_array($defaultLength, $this->lengths)) {
            $defaultLength = $this->lengths[0];
        }

        $this->defaultLength = $defaultLength;

        return $this;
    }
}
