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

namespace Spipu\UiBundle\Event;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Form;
use Symfony\Component\Form\FormInterface;

/**
 * Form Event
 */
class FormSaveEvent extends AbstractFormEvent
{
    public const PREFIX_NAME = 'spipu.ui.form.save.';

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var EntityInterface|null
     */
    private $resource;

    /**
     * FormSaveEvent constructor.
     * @param Form $formDefinition
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     */
    public function __construct(
        Form $formDefinition,
        FormInterface $form,
        EntityInterface $resource = null
    ) {
        parent::__construct($formDefinition);
        $this->form = $form;
        $this->resource = $resource;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return EntityInterface|null
     */
    public function getResource(): ?EntityInterface
    {
        return $this->resource;
    }
}
