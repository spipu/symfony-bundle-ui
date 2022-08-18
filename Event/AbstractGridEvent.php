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

namespace Spipu\UiBundle\Event;

use Spipu\UiBundle\Entity\Grid\Grid;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Grid Event
 */
abstract class AbstractGridEvent extends Event
{
    public const PREFIX_NAME = '';

    /**
     * @var Grid
     */
    private $gridDefinition;

    /**
     * GridEvent constructor.
     * @param Grid $gridDefinition
     */
    public function __construct(Grid $gridDefinition)
    {
        $this->gridDefinition = $gridDefinition;
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return static::PREFIX_NAME . $this->gridDefinition->getCode();
    }

    /**
     * @return Grid
     */
    public function getGridDefinition(): Grid
    {
        return $this->gridDefinition;
    }
}
