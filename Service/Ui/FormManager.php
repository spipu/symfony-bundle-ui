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
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormDefinitionEvent;
use Spipu\UiBundle\Event\FormSaveEvent;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\GenericType;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class FormManager implements FormManagerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SymfonyRequest
     */
    private $symfonyRequest;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityDefinitionInterface
     */
    private $definition;

    /**
     * @var Form
     */
    private $formDefinition;

    /**
     * @var EntityInterface
     */
    private $resource;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var string
     */
    private $submitLabel = 'spipu.ui.action.submit';

    /**
     * @var string
     */
    private $submitIcon = 'edit';

    /**
     * Manager constructor.
     * @param ContainerInterface $container
     * @param SymfonyRequest $symfonyRequest
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityDefinitionInterface $definition
     */
    public function __construct(
        ContainerInterface $container,
        SymfonyRequest $symfonyRequest,
        EventDispatcherInterface $eventDispatcher,
        EntityDefinitionInterface $definition
    ) {
        $this->container = $container;
        $this->symfonyRequest = $symfonyRequest;
        $this->eventDispatcher = $eventDispatcher;
        $this->definition = $definition;
    }

    /**
     * @param EntityInterface $resource
     * @return FormManagerInterface
     */
    public function setResource(EntityInterface $resource): FormManagerInterface
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return bool
     * @throws FormException
     */
    public function validate(): bool
    {
        $this->prepareForm();

        $this->form->handleRequest($this->symfonyRequest);

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            try {
                $this->definition->setSpecificFields($this->form, $this->resource);

                $event = new FormSaveEvent($this->formDefinition, $this->form, $this->resource);
                $this->eventDispatcher->dispatch($event, $event->getEventCode());

                if ($this->resource !== null) {
                    /** @var EntityManagerInterface $entityManager */
                    $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
                    $entityManager->persist($this->resource);
                    $entityManager->flush();
                }

                $this->addFlashTrans('success', 'spipu.ui.success.saved');

                return true;
            } catch (\Exception $e) {
                $this->addFlashTrans('danger', $e->getMessage());
            }
        }

        return false;
    }

    /**
     * @return void
     * @throws FormException
     */
    private function prepareForm(): void
    {
        $this->formDefinition = $this->definition->getDefinition();

        if (!$this->resource && $this->formDefinition->getEntityClassName() !== null) {
            throw new FormException('The Form Manager is not ready');
        }

        $event = new FormDefinitionEvent($this->formDefinition);
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $this->formDefinition->prepareSort();

        /** @var \Symfony\Component\Form\FormFactory $formFactory */
        $formFactory = $this->container->get('form.factory');

        $this->form = $formFactory->create(
            GenericType::class,
            $this->getFormData(),
            [
                'form_definition' => $this->formDefinition,
                'data_class'      => $this->formDefinition->getEntityClassName(),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => $this->formDefinition->getCode().'_item',
            ]
        );
    }

    /**
     * @return array|EntityInterface|null
     */
    private function getFormData()
    {
        $data = $this->resource;

        if ($data === null) {
            $values = $this->getFieldValues();
            if (!empty($values)) {
                return $values;
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getFieldValues(): array
    {
        $values = [];

        foreach ($this->formDefinition->getFieldSets() as $fieldSet) {
            foreach ($fieldSet->getFields() as $field) {
                if ($field->getValue() !== null && !$field->isHiddenInForm()) {
                    $values[$field->getCode()] = $field->getValue();
                }
            }
        }

        return $values;
    }

    /**
     * @param string $type
     * @param string $message
     * @param array $params
     * @return void
     */
    private function addFlashTrans(string $type, string $message, array $params = []): void
    {
        $this->addFlash($type, $this->trans($message, $params));
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    private function addFlash(string $type, string $message): void
    {
        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @param string $message
     * @param array $params
     * @return string
     */
    private function trans(string $message, array $params = []): string
    {
        return $this->container->get('translator')->trans($message, $params);
    }

    /**
     * @return EntityInterface
     */
    public function getResource(): EntityInterface
    {
        return $this->resource;
    }

    /**
     * @return string
     * @throws \Twig_Error
     */
    public function display(): string
    {
        return $this->container->get('twig')->render(
            $this->formDefinition->getTemplateForm(),
            [
                'manager' => $this,
            ]
        );
    }

    /**
     * @return FormView
     */
    public function getFormView(): FormView
    {
        return $this->form->createView();
    }

    /**
     * @return string
     */
    public function getSubmitLabel(): string
    {
        return $this->submitLabel;
    }

    /**
     * @return string
     */
    public function getSubmitIcon(): string
    {
        return $this->submitIcon;
    }

    /**
     * @param string $submitLabel
     * @param string $submitIcon
     * @return FormManagerInterface
     */
    public function setSubmitButton(string $submitLabel, string $submitIcon = 'edit'): FormManagerInterface
    {
        $this->submitLabel = $submitLabel;
        $this->submitIcon = $submitIcon;

        return $this;
    }

    /**
     * @return FieldSet[]
     */
    public function getFieldSets(): array
    {
        return $this->formDefinition->getFieldSets();
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return Form
     */
    public function getDefinition(): Form
    {
        return $this->formDefinition;
    }
}
