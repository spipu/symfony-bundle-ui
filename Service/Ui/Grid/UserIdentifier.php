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

class UserIdentifier implements UserIdentifierInterface
{
    /**
     * @param UserInterface|null $user
     * @return string
     */
    public function getIdentifier(?UserInterface $user): string
    {
        if (method_exists($user, 'getId') && !empty($user->getId())) {
            return 'user_' . $user->getId();
        }

        return md5($user->getUserIdentifier());
    }
}
