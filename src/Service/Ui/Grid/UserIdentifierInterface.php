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

namespace Spipu\UiBundle\Service\Ui\Grid;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserIdentifierInterface
{
    public function getIdentifier(?UserInterface $user): string;
}
