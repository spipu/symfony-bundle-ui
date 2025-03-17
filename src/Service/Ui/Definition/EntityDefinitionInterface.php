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

namespace Spipu\UiBundle\Service\Ui\Definition;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Form;
use Symfony\Component\Form\FormInterface;

interface EntityDefinitionInterface
{
    public function getDefinition(): Form;

    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void;
}
