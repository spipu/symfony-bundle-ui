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

abstract class AbstractGridEvent extends Event
{
    public const PREFIX_NAME = '';

    private Grid $gridDefinition;

    public function __construct(Grid $gridDefinition)
    {
        $this->gridDefinition = $gridDefinition;
    }

    public function getEventCode(): string
    {
        return static::PREFIX_NAME . $this->gridDefinition->getCode();
    }

    public function getGridDefinition(): Grid
    {
        return $this->gridDefinition;
    }
}
