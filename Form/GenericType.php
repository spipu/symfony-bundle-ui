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

namespace Spipu\UiBundle\Form;

use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericType extends AbstractType
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->form = $options['form_definition'];

        foreach ($this->form->getFieldSets() as $fieldSet) {
            if ($fieldSet->isHiddenInForm()) {
                continue;
            }
            foreach ($fieldSet->getFields() as $field) {
                if ($field->isHiddenInForm()) {
                    continue;
                }
                $this->prepareField($builder, $field);
            }
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Field $field
     * @return void
     */
    private function prepareField(FormBuilderInterface $builder, Field $field): void
    {
        $opts = $field->getOptions();
        if (array_key_exists('choices', $opts)) {
            /** @var OptionsInterface $choices */
            $choices = $opts['choices'];
            $required = empty($opts['required']);

            $opts['choices'] = $required ? $choices->getOptionsWithEmptyValueInverse() : $choices->getOptionsInverse();
        }

        $builder->add($field->getCode(), $field->getType(), $opts);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('form_definition');
    }
}
