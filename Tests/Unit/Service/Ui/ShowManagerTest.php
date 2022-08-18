<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Ui;

use Spipu\UiBundle\Event\FormDefinitionEvent;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Service\Ui\ShowFactory;
use Spipu\UiBundle\Service\Ui\ShowManager;
use Spipu\UiBundle\Service\Ui\ShowManagerInterface;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ShowManagerTest extends AbstractTest
{
    /**
     * @param ContainerInterface $container
     * @return ShowFactory
     */
    private function getShowFactory(ContainerInterface $container): ShowFactory
    {
        return new ShowFactory(
            $container->get('event_dispatcher'),
            $container->get('twig')
        );
    }

    public function testManager()
    {
        $container = $this->getContainerMock();
        $definition = SpipuUiMock::getEntityDefinitionMock();

        $factory = $this->getShowFactory($container);

        /** @var ShowManager $manager */
        $manager = $factory->create($definition);

        $this->assertInstanceOf(ShowManagerInterface::class, $manager);

        $resource = SpipuUiMock::getResourceMock();

        $manager->setResource($resource);
        $this->assertSame($resource, $manager->getResource());

        $eventDispatcher = $container->get('event_dispatcher');
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->anything(), FormDefinitionEvent::PREFIX_NAME . $definition->getDefinition()->getCode());

        $this->assertTrue($manager->validate());
        $this->assertSame($definition->getDefinition()->getFieldSets(), $manager->getFieldSets());

        $twig = $container->get('twig');
        $twig
            ->expects($this->once())
            ->method('render')
            ->with($definition->getDefinition()->getTemplateView(), ['manager' => $manager])
            ->willReturn('From template');

        $this->assertSame('From template', $manager->display());
    }

    public function testMissingResource()
    {
        $container = $this->getContainerMock();
        $definition = SpipuUiMock::getEntityDefinitionMock();

        $factory = $this->getShowFactory($container);
        $manager = $factory->create($definition);

        $this->expectException(FormException::class);
        $manager->validate();
    }
}
