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

namespace Spipu\UiBundle\Service\Ui\Definition;

use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;

interface GridDefinitionInterface
{
    public function getDefinition(): GridDefinition;
}
