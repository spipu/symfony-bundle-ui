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

use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as Twig;

class ShowFactory
{
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
     * @param EventDispatcherInterface $eventDispatcher
     * @param Twig $twig
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Twig $twig
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
    }

    /**
     * @param EntityDefinitionInterface $formDefinition
     * @return ShowManagerInterface
     */
    public function create(EntityDefinitionInterface $formDefinition): ShowManagerInterface
    {
        return new ShowManager(
            $this->eventDispatcher,
            $this->twig,
            $formDefinition
        );
    }
}
