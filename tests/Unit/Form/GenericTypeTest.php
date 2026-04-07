<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Form;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UiBundle\Form\GenericType;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(GenericType::class)]
class GenericTypeTest extends TestCase
{

    public function testForm(): void
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
        $matcher = $this->exactly(3);
        $formBuilder
            ->expects($matcher)
            ->method('add')
            ->willReturnCallback(function (string $child, ?string $type = null, array $fieldOptions = []) use ($matcher, $formBuilder, $options): FormBuilderInterface {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertSame(['field_2_2', Type\TextType::class, ['label' => 'Field 2.2']], [$child, $type, $fieldOptions]),
                    2 => $this->assertSame(
                        ['field_2_3', Type\ChoiceType::class, ['label' => 'Field 2.3', 'required' => false, 'choices' => $options->getOptionsWithEmptyValueInverse()]],
                        [$child, $type, $fieldOptions]
                    ),
                    3 => $this->assertSame(
                        ['field_2_4', Type\ChoiceType::class, ['label' => 'Field 2.4', 'required' => true, 'choices' => $options->getOptionsInverse()]],
                        [$child, $type, $fieldOptions]
                    ),
                };
                return $formBuilder;
            });

        $form->buildForm($formBuilder, ['form_definition' => $definition]);
    }
}
