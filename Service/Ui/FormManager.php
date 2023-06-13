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

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Event\FormDefinitionEvent;
use Spipu\UiBundle\Event\FormSaveEvent;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\GenericType;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class FormManager implements FormManagerInterface
{
    private SymfonyRequest $symfonyRequest;
    private EventDispatcherInterface $eventDispatcher;
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private TranslatorInterface $translator;
    private Twig $twig;
    private EntityDefinitionInterface $definition;
    private ?Form $formDefinition = null;
    private ?EntityInterface $resource = null;
    private ?FormInterface $form = null;
    private string $submitLabel = 'spipu.ui.action.submit';
    private string $submitIcon = 'edit';

    public function __construct(
        SymfonyRequest $symfonyRequest,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        Twig $twig,
        EntityDefinitionInterface $definition
    ) {
        $this->symfonyRequest = $symfonyRequest;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->twig = $twig;
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
                    $this->entityManager->persist($this->resource);
                    $this->entityManager->flush();
                }

                $message = $this->definition->getDefinition()->getValidateSuccessMessage();
                if ($message != '') {
                    $this->addFlashTrans('success', $message);
                }

                return true;
            } catch (Exception $e) {
                $this->addFlashTrans('danger', $e->getMessage());
            }
        }

        return false;
    }

    private function prepareForm(): void
    {
        $this->formDefinition = $this->definition->getDefinition();

        if (!$this->resource && $this->formDefinition->getEntityClassName() !== null) {
            throw new FormException('The Form Manager is not ready');
        }

        $event = new FormDefinitionEvent($this->formDefinition);
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $this->formDefinition->prepareSort();

        $this->form = $this->formFactory->create(
            GenericType::class,
            $this->getFormData(),
            [
                'form_definition' => $this->formDefinition,
                'data_class'      => $this->formDefinition->getEntityClassName(),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'csrf_token_id'   => $this->formDefinition->getCode() . '_item',
            ]
        );
    }

    private function getFormData(): EntityInterface|array|null
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

    private function addFlashTrans(string $type, string $message, array $params = []): void
    {
        $this->addFlash($type, $this->trans($message, $params));
    }

    private function addFlash(string $type, string $message): void
    {
        $this->symfonyRequest->getSession()->getFlashBag()->add($type, $message);
    }

    private function trans(string $message, array $params = []): string
    {
        return $this->translator->trans($message, $params);
    }

    public function getResource(): ?EntityInterface
    {
        return $this->resource;
    }

    public function display(): string
    {
        return $this->twig->render(
            $this->formDefinition->getTemplateForm(),
            [
                'manager' => $this,
            ]
        );
    }

    public function getFormView(): FormView
    {
        return $this->form->createView();
    }

    public function getSubmitLabel(): string
    {
        return $this->submitLabel;
    }

    public function getSubmitIcon(): string
    {
        return $this->submitIcon;
    }

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

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getDefinition(): Form
    {
        return $this->formDefinition;
    }
}
