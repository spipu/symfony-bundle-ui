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

use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment as Twig;

class GridFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * GridFactory constructor.
     * @param ContainerInterface $container
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EventDispatcherInterface $eventDispatcher
     * @param Twig $twig
     */
    public function __construct(
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        Twig $twig
    ) {
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
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
            $this->authorizationChecker,
            $this->container->get('router'),
            $this->eventDispatcher,
            $this->twig,
            $gridDefinition
        );
    }
}
