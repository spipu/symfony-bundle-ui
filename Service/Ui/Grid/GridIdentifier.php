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

use Exception;
use Spipu\UiBundle\Entity\Grid\Grid;
use Symfony\Component\HttpFoundation\RequestStack;

class GridIdentifier implements GridIdentifierInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param Grid $grid
     * @return string
     */
    public function getIdentifier(Grid $grid): string
    {
        return $this->getRoute() . '/' . $grid->getCode();
    }

    /**
     * @return string
     */
    private function getRoute(): string
    {
        try {
            return (string) $this->requestStack->getCurrentRequest()->attributes->get('_route');
        } catch (Exception $e) {
            return 'no_route';
        }
    }
}
