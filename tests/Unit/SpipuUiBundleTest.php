<?php
namespace Spipu\UiBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\SpipuUiBundle;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;

class SpipuUiBundleTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);

        $bundle = new SpipuUiBundle();

        $this->assertInstanceOf(ConfigurableExtensionInterface::class, $bundle);

        $bundle->loadExtension([], $configurator, $builder);
        $bundle->prependExtension($configurator, $builder);

        $this->assertSame(
            [0 => ['form_themes' => ['@SpipuUi/form_layout.html.twig']]],
            $builder->getExtensionConfig('twig')
        );
    }
}