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

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormDefinitionEvent;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as Twig;

class ShowManager implements ShowManagerInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private Twig $twig;
    private EntityDefinitionInterface $definition;
    private Form $formDefinition;
    private ?EntityInterface $resource = null;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Twig $twig,
        EntityDefinitionInterface $definition
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
        $this->definition = $definition;
    }

    public function setResource(EntityInterface $resource): ShowManagerInterface
    {
        $this->resource = $resource;

        return $this;
    }

    public function validate(): bool
    {
        if (!$this->resource) {
            throw new FormException('The Show Manager is not ready');
        }

        $this->formDefinition = $this->definition->getDefinition();

        $event = new FormDefinitionEvent($this->formDefinition);
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $this->formDefinition->prepareSort();

        return true;
    }

    public function getResource(): EntityInterface
    {
        return $this->resource;
    }

    public function display(): string
    {
        return $this->twig->render(
            $this->formDefinition->getTemplateView(),
            [
                'manager' => $this,
            ]
        );
    }

    /**
     * @return FieldSet[]
     */
    public function getFieldSets(): array
    {
        return $this->formDefinition->getFieldSets();
    }

    public function getDefinition(): Form
    {
        return $this->formDefinition;
    }
}
