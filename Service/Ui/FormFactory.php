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

use Doctrine\ORM\EntityManagerInterface;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;

class FormFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * GridFactory constructor.
     * @param ContainerInterface $container
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface $translator
     * @param Twig $twig
     */
    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        Twig $twig
    ) {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->twig = $twig;
    }

    /**
     * @param EntityDefinitionInterface $formDefinition
     * @return FormManagerInterface
     */
    public function create(EntityDefinitionInterface $formDefinition): FormManagerInterface
    {
        return new FormManager(
            $this->container->get('request_stack')->getCurrentRequest(),
            $this->eventDispatcher,
            $this->entityManager,
            $this->formFactory,
            $this->translator,
            $this->twig,
            $formDefinition
        );
    }
}
