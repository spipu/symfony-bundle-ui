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
use Spipu\UiBundle\Entity\Form\Form;
use Symfony\Component\Form\FormInterface;

interface FormManagerInterface extends UiManagerInterface
{
    /**
     * @param EntityInterface $resource
     * @return FormManagerInterface
     */
    public function setResource(EntityInterface $resource): FormManagerInterface;

    /**
     * @param string $submitLabel
     * @param string $submitIcon
     * @return FormManagerInterface
     */
    public function setSubmitButton(string $submitLabel, string $submitIcon = 'edit'): FormManagerInterface;

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * @return Form
     */
    public function getDefinition(): Form;
}
