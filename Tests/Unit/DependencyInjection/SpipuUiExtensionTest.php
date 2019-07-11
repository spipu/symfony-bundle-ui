<?php
namespace Spipu\UiBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\DependencyInjection\SpipuUiExtension;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class SpipuUiExtensionTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuUiExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);

        $extension->load([], $builder);
        $extension->prepend($builder);

        $this->assertSame(
            [0 => ['form_themes' => ['@SpipuUi/form_layout.html.twig']]],
            $builder->getExtensionConfig('twig')
        );
    }
}