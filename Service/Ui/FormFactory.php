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

class FormFactory
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
     * @return FormManagerInterface
     */
    public function create(EntityDefinitionInterface $formDefinition): FormManagerInterface
    {
        return new FormManager(
            $this->container,
            $this->container->get('request_stack')->getCurrentRequest(),
            $this->container->get('event_dispatcher'),
            $formDefinition
        );
    }
}
