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

namespace Spipu\UiBundle\Service\Ui;

use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GridFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * GridFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param GridDefinitionInterface $gridDefinition
     * @return GridManagerInterface
     * @throws GridException
     */
    public function create(GridDefinitionInterface $gridDefinition): GridManagerInterface
    {
        return new GridManager(
            $this->container,
            $this->container->get('request_stack')->getCurrentRequest(),
            $this->container->get('session'),
            $this->container->get('security.authorization_checker'),
            $this->container->get('router'),
            $this->container->get('event_dispatcher'),
            $gridDefinition
        );
    }
}
