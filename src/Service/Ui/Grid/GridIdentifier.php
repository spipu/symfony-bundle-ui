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

use Spipu\UiBundle\Entity\Grid\Grid;
use Symfony\Component\HttpFoundation\RequestStack;

class GridIdentifier implements GridIdentifierInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getIdentifier(Grid $grid): string
    {
        return $this->getRoute() . '/' . $grid->getCode();
    }

    private function getRoute(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest ? (string) $currentRequest->attributes->get('_route', 'no_route') : 'no_route';
    }
}
