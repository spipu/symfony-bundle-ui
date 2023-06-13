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

use Spipu\UiBundle\Entity\Form\Form;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractFormEvent extends Event
{
    public const PREFIX_NAME = '';

    private Form $formDefinition;

    public function __construct(Form $formDefinition)
    {
        $this->formDefinition = $formDefinition;
    }

    public function getEventCode(): string
    {
        return static::PREFIX_NAME . $this->formDefinition->getCode();
    }

    public function getFormDefinition(): Form
    {
        return $this->formDefinition;
    }
}
