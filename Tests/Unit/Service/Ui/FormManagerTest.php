<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui;

use PHPUnit\Framework\MockObject\MockObject;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Event\FormDefinitionEvent;
use Spipu\UiBundle\Event\FormSaveEvent;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\FormManager;
use Spipu\UiBundle\Service\Ui\FormManagerInterface;
use Spipu\UiBundle\Tests\ResourceMock;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class FormManagerTest extends AbstractTest
{
    /**
     * @param ContainerInterface $container
     * @return FormFactory
     */
    private function getFormFactory(ContainerInterface $container): FormFactory
    {
        return new FormFactory(
            $container,
            $container->get('event_dispatcher'),
            $container->get('doctrine.orm.default_entity_manager'),
            $container->get('form.factory'),
            $container->get('translator'),
            $container->get('twig')
        );
    }

    public function testManagerWithoutResource()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        $definition = SpipuUiMock::getEntityDefinitionMock();

        $factory = $this->getFormFactory($container);

        /** @var FormManager $manager */
        $manager = $factory->create($definition);
        $this->assertInstanceOf(FormManagerInterface::class, $manager);

        $this->assertSame('spipu.ui.action.submit', $manager->getSubmitLabel());
        $this->assertSame('edit', $manager->getSubmitIcon());

        $manager->setSubmitButton('test_submit', 'test_icon');
        $this->assertSame('test_submit', $manager->getSubmitLabel());
        $this->assertSame('test_icon', $manager->getSubmitIcon());

        /** @var MockObject $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->anything(), FormDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode());

        $this->assertFalse($manager->validate());

        $this->assertInstanceOf(\Symfony\Component\Form\FormInterface::class, $manager->getForm());
        $this->assertInstanceOf(\Symfony\Component\Form\FormView::class, $manager->getFormView());
        $this->assertSame($definition->getDefinition(), $manager->getDefinition());
        $this->assertSame($definition->getDefinition()->getFieldSets(), $manager->getFieldSets());

        $twig = $container->get('twig');
        $twig
            ->expects($this->once())
            ->method('render')
            ->with($definition->getDefinition()->getTemplateForm(), ['manager' => $manager])
            ->willReturn('From template');

        $this->assertSame('From template', $manager->display());

        $this->assertSame(
            ['field_a_a' => 'test', 'field_b_b' => 'not_called'],
            $manager->getForm()->getData()
        );
    }

    public function testManagerWithoutResourceSubmit()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        $definition = SpipuUiMock::getEntityDefinitionMock();

        $values = [
            'generic' => [
                'field_a_a' => 'Value a.a',
                'field_b_a' => 'Value b.a',
                'field_b_b' => 'Value b.b',
                '_token'    => 'mock_token_value'
            ]
        ];

        /** @var Request $request */
        $request = $container->get('request_stack')->getCurrentRequest();
        $request->initialize([], $values);
        $request->setMethod('POST');

        $factory = $this->getFormFactory($container);

        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->anything(), FormDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode()],
                [$this->anything(), FormSaveEvent::PREFIX_NAME . $definition->getDefinition()->getCode()]
            );

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        /** @var FormManager $manager */
        $manager = $factory->create($definition);
        $this->assertTrue($manager->validate());

        $this->assertSame(true, $manager->getForm()->isSubmitted());
        $this->assertSame(true, $manager->getForm()->isValid());
        $this->assertSame(['success' => ['spipu.ui.success.saved']], $container->get('request_stack')->getSession()->getFlashBag()->all());

        $this->assertSame($values['generic']['field_a_a'], $manager->getForm()->getData()['field_a_a']);
        $this->assertSame($values['generic']['field_b_a'], $manager->getForm()->getData()['field_b_a']);
        $this->assertSame($values['generic']['field_b_b'], $manager->getForm()->getData()['field_b_b']);
    }

    public function testManagerWithResource()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        $resource = SpipuUiMock::getResourceMock();
        $resource->setFieldAB('original');

        $definition = SpipuUiMock::getEntityDefinitionMock();
        $definition->getDefinition()->setEntityClassName(ResourceMock::class);

        $factory = $this->getFormFactory($container);

        /** @var FormManager $manager */
        $manager = $factory->create($definition);
        $this->assertInstanceOf(FormManagerInterface::class, $manager);

        /** @var EntityInterface $resource */
        $manager->setResource($resource);
        $this->assertSame($resource, $manager->getResource());

        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->anything(), FormDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode());

        $this->assertFalse($manager->validate());

        $this->assertInstanceOf(\Symfony\Component\Form\FormInterface::class, $manager->getForm());
        $this->assertInstanceOf(\Symfony\Component\Form\FormView::class, $manager->getFormView());
        $this->assertSame($definition->getDefinition(), $manager->getDefinition());
        $this->assertSame($definition->getDefinition()->getFieldSets(), $manager->getFieldSets());

        $twig = $container->get('twig');
        $twig
            ->expects($this->once())
            ->method('render')
            ->with($definition->getDefinition()->getTemplateForm(), ['manager' => $manager])
            ->willReturn('From template');

        $this->assertSame('From template', $manager->display());

        $this->assertSame($resource, $manager->getForm()->getData());
        $this->assertSame(0, $resource->getFieldAA());
        $this->assertSame('original', $resource->getFieldAB());
        $this->assertSame('', $resource->getFieldBA());
        $this->assertSame('', $resource->getFieldBB());
    }

    public function testManagerWithResourceSubmit()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        $resource = SpipuUiMock::getResourceMock();
        $resource->setFieldAB('original');

        $definition = SpipuUiMock::getEntityDefinitionMock();
        $definition->getDefinition()->setEntityClassName(ResourceMock::class);

        $values = [
            'generic' => [
                'field_a_a' => 42,
                'field_b_a' => 'Value b.a',
                'field_b_b' => 'Value b.b',
                '_token'    => 'mock_token_value'
            ]
        ];

        /** @var Request $request */
        $request = $container->get('request_stack')->getCurrentRequest();
        $request->initialize([], $values);
        $request->setMethod('POST');

        $factory = $this->getFormFactory($container);

        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->anything(), FormDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode()],
                [$this->anything(), FormSaveEvent::PREFIX_NAME . $definition->getDefinition()->getCode()]
            );

        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $entityManager->expects($this->once())->method('persist')->with($resource);
        $entityManager->expects($this->once())->method('flush');

        /** @var FormManager $manager */
        $manager = $factory->create($definition);
        $manager->setResource($resource);

        $this->assertTrue($manager->validate());

        $this->assertSame(true, $manager->getForm()->isSubmitted());
        $this->assertSame(true, $manager->getForm()->isValid());
        $this->assertSame(['success' => ['spipu.ui.success.saved']], $container->get('request_stack')->getSession()->getFlashBag()->all());

        $this->assertSame($resource, $manager->getForm()->getData());
        $this->assertSame(null, $resource->getId());
        $this->assertSame(42, $resource->getFieldAA());
        $this->assertSame('original', $resource->getFieldAB());
        $this->assertSame('Value b.a', $resource->getFieldBA());
        $this->assertSame('called', $resource->getFieldBB());
    }

    public function testManagerWithResourceError()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        SpipuUiMock::getResourceMock();

        $definition = SpipuUiMock::getEntityDefinitionMock();
        $definition->getDefinition()->setEntityClassName(ResourceMock::class);

        $factory = $this->getFormFactory($container);

        /** @var FormManager $manager */
        $manager = $factory->create($definition);
        $this->assertInstanceOf(FormManagerInterface::class, $manager);

        $this->expectException(FormException::class);
        $manager->validate();
    }

    public function testManagerSubmitError()
    {
        $container = $this->getContainerMock(['form.factory' => SymfonyMock::getFormFactory($this)]);

        $definition = SpipuUiMock::getEntityDefinitionMock();

        $values = [
            'generic' => [
                'field_a_a' => 'error',
                '_token'    => 'mock_token_value'
            ]
        ];

        /** @var Request $request */
        $request = $container->get('request_stack')->getCurrentRequest();
        $request->initialize([], $values);
        $request->setMethod('POST');

        $factory = $this->getFormFactory($container);

        /** @var FormManager $manager */
        $manager = $factory->create($definition);

        $this->assertFalse($manager->validate());
        $this->assertSame(['danger' => ['mock error']], $container->get('request_stack')->getSession()->getFlashBag()->all());
    }
}
