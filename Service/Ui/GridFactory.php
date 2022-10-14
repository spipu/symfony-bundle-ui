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
use Spipu\UiBundle\Service\Ui\Grid\GridConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment as Twig;

class GridFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var GridConfig
     */
    private $gridConfig;

    /**
     * GridFactory constructor.
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param Twig $twig
     * @param GridConfig $gridConfig
     */
    public function __construct(
        ContainerInterface $container,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        Twig $twig,
        GridConfig $gridConfig
    ) {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
        $this->gridConfig = $gridConfig;
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
            $this->requestStack->getCurrentRequest(),
            $this->authorizationChecker,
            $this->router,
            $this->eventDispatcher,
            $this->twig,
            $this->gridConfig,
            $gridDefinition
        );
    }
}
