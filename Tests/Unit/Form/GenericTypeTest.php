<?php
namespace Spipu\UiBundle\Tests\Unit\Form;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UiBundle\Form\GenericType;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericTypeTest extends TestCase
{

    public function testForm()
    {
        $form = new GenericType();

        $optionsResolver = $this->createMock(OptionsResolver::class);
        $optionsResolver->expects($this->once())->method('setDefined')->with('form_definition');

        $form->configureOptions($optionsResolver);

        $options = SpipuUiMock::getOptionStringMock();

        $definition = new Form\Form('test');
        $definition
            ->addFieldSet(
                (new Form\FieldSet('fieldset_1', 'FieldSet 1', 10))
                    ->useHiddenInForm(true)
                    ->addField(
                        (new Form\Field(
                            'field_1_1',
                            Type\TextType::class,
                            10,
                            ['label' => 'Field 1.1']
                        ))
                    )
            )
            ->addFieldSet(
                (new Form\FieldSet('fieldset_2', 'FieldSet 2', 20))
                    ->addField(
                        (new Form\Field(
                            'field_2_1',
                            Type\TextType::class,
                            10,
                            ['label' => 'Field 2.1']
                        ))->useHiddenInForm(true)
                    )
                    ->addField(
                        (new Form\Field(
                            'field_2_2',
                            Type\TextType::class,
                            20,
                            ['label' => 'Field 2.2']
                        ))
                    )
                    ->addField(
                        (new Form\Field(
                            'field_2_3',
                            Type\ChoiceType::class,
                            30,
                            ['label' => 'Field 2.3', 'required' => false, 'choices' => $options]
                        ))
                    )
                    ->addField(
                        (new Form\Field(
                            'field_2_4',
                            Type\ChoiceType::class,
                            40,
                            ['label' => 'Field 2.4', 'required' => true, 'choices' => $options]
                        ))
                    )
            )
        ;

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $formBuilder
            ->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                [
                    $this->equalTo('field_2_2'),
                    $this->equalTo(Type\TextType::class),
                    $this->equalTo(
                        ['label' => 'Field 2.2']
                    ),
                ],
                [
                    $this->equalTo('field_2_3'),
                    $this->equalTo(Type\ChoiceType::class),
                    $this->equalTo(
                        [
                            'label' => 'Field 2.3',
                            'required' => false,
                            'choices' => $options->getOptionsWithEmptyValueInverse()
                        ]
                    ),
                ],
                [
                    $this->equalTo('field_2_4'),
                    $this->equalTo(Type\ChoiceType::class),
                    $this->equalTo(
                        [
                            'label' => 'Field 2.4',
                            'required' => true,
                            'choices' => $options->getOptionsInverse()
                        ]
                    ),
                ]
            );

        $form->buildForm($formBuilder, ['form_definition' => $definition]);
    }
}
