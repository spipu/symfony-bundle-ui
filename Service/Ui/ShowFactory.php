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

use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ShowFactory
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
     * @param EntityDefinitionInterface $formDefinition
     * @return ShowManagerInterface
     */
    public function create(EntityDefinitionInterface $formDefinition): ShowManagerInterface
    {
        return new ShowManager(
            $this->container,
            $this->container->get('event_dispatcher'),
            $formDefinition
        );
    }
}
