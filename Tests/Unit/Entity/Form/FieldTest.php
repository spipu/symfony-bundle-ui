<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Form;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Form\Options\YesNo;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class FieldTest extends TestCase
{
    public function testEntity()
    {
        $entity = new Form\Field('code', 'text', 10, []);
        $this->assertSame('code', $entity->getCode());
        $this->assertSame('text', $entity->getType());
        $this->assertSame(10, $entity->getPosition());

        $this->assertSame(false, $entity->isList());
        $entity->useList();
        $this->assertSame(true, $entity->isList());
        $entity->useList(false);
        $this->assertSame(false, $entity->isList());

        $this->assertSame(false, $entity->isHiddenInForm());
        $entity->useHiddenInForm();
        $this->assertSame(true, $entity->isHiddenInForm());
        $entity->useHiddenInForm(false);
        $this->assertSame(false, $entity->isHiddenInForm());

        $this->assertSame(false, $entity->isHiddenInView());
        $entity->useHiddenInView();
        $this->assertSame(true, $entity->isHiddenInView());
        $entity->useHiddenInView(false);
        $this->assertSame(false, $entity->isHiddenInView());

        $this->assertSame(null, $entity->getValue());
        $entity->setValue('test');
        $this->assertSame('test', $entity->getValue());
    }

    public function testEntityTemplate()
    {
        $entity = new Form\Field('code', Type\DateType::class, 10, []);
        $this->assertSame('@SpipuUi/entity/view/date.html.twig', $entity->getTemplateView());

        $entity = new Form\Field('code', Type\DateTimeType::class, 10, []);
        $this->assertSame('@SpipuUi/entity/view/datetime.html.twig', $entity->getTemplateView());

        $entity = new Form\Field('code', EntityType::class, 10, []);
        $this->assertSame('@SpipuUi/entity/view/entity.html.twig', $entity->getTemplateView());

        $entity = new Form\Field('code', Type\ChoiceType::class, 10, []);
        $this->assertSame('@SpipuUi/entity/view/select.html.twig', $entity->getTemplateView());

        $entity = new Form\Field('code', Type\TextType::class, 10, []);
        $this->assertSame('@SpipuUi/entity/view/text.html.twig', $entity->getTemplateView());

        $entity->setTemplateView('view.html.twig');
        $this->assertSame('view.html.twig', $entity->getTemplateView());
    }

    public function testEntityLabel()
    {
        $entity = new Form\Field('code', Type\TextType::class, 10, []);
        $this->assertSame(null, $entity->getLabel());

        $entity = new Form\Field('code', Type\TextType::class, 10, ['label' => 'test']);
        $this->assertSame('test', $entity->getLabel());

        $entity->addOption('label', 'other');
        $this->assertSame('other', $entity->getLabel());
    }

    public function testEntityChoice()
    {
        $yesNo = new YesNo();

        $entity = new Form\Field('code', Type\ChoiceType::class, 10, ['choices' => $yesNo]);
        $this->assertSame($yesNo, $entity->getChoices());

        $entity = new Form\Field('code', Type\TextType::class, 10, []);
        $this->assertSame(null, $entity->getChoices());

        $entity->addOption('choices', $yesNo);
        $this->assertSame($yesNo, $entity->getChoices());

        $this->expectException(FormException::class);
        $entity->addOption('choices', new \stdClass());
    }

    public function testEntityOptions()
    {
        $entity = new Form\Field('code', Type\TextType::class, 10, []);
        $this->assertSame([], $entity->getOptions());

        $entity = new Form\Field('code', Type\TextType::class, 10, ['a' => 1]);
        $this->assertSame(['a' => 1], $entity->getOptions());
    }
}
