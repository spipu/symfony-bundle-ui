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

/**
 * Form Event
 */
class FormDefinitionEvent extends AbstractFormEvent
{
    public const PREFIX_NAME = 'spipu.ui.form.definition.';
}
