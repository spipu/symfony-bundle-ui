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

class FormSaveEvent extends AbstractFormEvent
{
    public const PREFIX_NAME = 'spipu.ui.form.save.';

    private FormInterface $form;
    private ?EntityInterface $resource;

    public function __construct(
        Form $formDefinition,
        FormInterface $form,
        EntityInterface $resource = null
    ) {
        parent::__construct($formDefinition);
        $this->form = $form;
        $this->resource = $resource;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getResource(): ?EntityInterface
    {
        return $this->resource;
    }
}
