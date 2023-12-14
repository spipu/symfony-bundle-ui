<?php
namespace Spipu\UiBundle\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\Manager as MenuManager;
use Spipu\UiBundle\Service\Ui\UiManagerInterface;
use Spipu\UiBundle\Twig\UiExtension;
use Twig\Extension\ExtensionInterface;

class UiExtensionTest extends TestCase
{
    public function getExtension(): UiExtension
    {
        $menuManagerMock = $this->createMock(MenuManager::class);

        $menuManagerMock->expects($this->any())
            ->method('buildMenu')
            ->will(
                $this->returnCallback(
                    function (string $currentItemCode = '') {
                        $item = new Item($currentItemCode, $currentItemCode);
                        $item->setActive(true);
                        return $item;
                    }
                )
            );

        /** @var MenuManager $menuManagerMock */
        $extension = new UiExtension($menuManagerMock, SymfonyMock::getTranslator($this));

        return $extension;
    }

    public function testExtension()
    {
        $extension = $this->getExtension();
        $this->assertTrue($extension instanceof ExtensionInterface);
    }

    public function testFunctions()
    {
        $allowedNames = [
            'renderManager',
            'getMenu',
            'getTranslations',
        ];

        $extension = $this->getExtension();
        $functions = $extension->getFunctions();

        $foundNames = [];
        foreach ($functions as $function) {
            $this->assertTrue(in_array($function->getName(), $allowedNames, true));
            $this->assertTrue(call_user_func_array('method_exists', $function->getCallable()));
            $foundNames[] = $function->getName();
        }

        $this->assertSame(count($allowedNames), count($foundNames));
    }

    public function testGetMenu()
    {
        Item::resetAll();

        $extension = $this->getExtension();
        $item = $extension->getMenu('test');

        $this->assertSame(1, $item->getId());
        $this->assertSame('test', $item->getCode());
        $this->assertSame(true, $item->isActive());

        Item::resetAll();
    }

    public function testRenderManager()
    {
        $mockManager = $this->createMock(UiManagerInterface::class);
        $mockManager
            ->expects($this->once())
            ->method('display')
            ->will(
                $this->returnCallback(
                    function () {
                        return 'test';
                    }
                )
            );

        $extension = $this->getExtension();

        ob_start();
        $extension->renderManager($mockManager);
        $result = ob_get_clean();

        $this->assertSame('test', $result);
    }
}
