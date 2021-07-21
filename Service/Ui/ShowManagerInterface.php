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

namespace Spipu\UiBundle\Service\Ui;

use Spipu\UiBundle\Entity\EntityInterface;

interface ShowManagerInterface extends UiManagerInterface
{
    /**
     * @param EntityInterface $resource
     * @return ShowManagerInterface
     */
    public function setResource(EntityInterface $resource): ShowManagerInterface;
}
