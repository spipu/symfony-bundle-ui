<?php
namespace Spipu\UiBundle\Tests\Unit\Service\Menu;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\DefinitionInterface;
use Spipu\UiBundle\Service\Menu\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ManagerTest extends TestCase
{
    public function testServiceActive()
    {
        Item::resetAll();

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $definition = new MenuDefinition();

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $service = new Manager($authorizationChecker, $definition);
        $menu = $service->buildMenu('child_1_1');

        $this->assertSame('test', $menu->getCode());

        $this->assertTrue($menu->getChildItems()[0]->isActive());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[0]->isActive());
        $this->assertFalse($menu->getChildItems()[0]->getChildItems()[1]->isActive());
        $this->assertFalse($menu->getChildItems()[1]->isActive());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[0]->isActive());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[1]->isActive());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[2]->isActive());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[3]->isActive());

        Item::resetAll();
    }

    public function testServiceAllowedNotConnected()
    {
        Item::resetAll();

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->atLeastOnce())
            ->method('isGranted')
            ->will(
                $this->returnValueMap(
                    [
                        ['IS_AUTHENTICATED_REMEMBERED', null, false],
                        ['ROLE_TEST', null, false],
                    ]
                )
            );

        $definition = new MenuDefinition();

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $service = new Manager($authorizationChecker, $definition);
        $menu = $service->buildMenu('child_1_1');

        $this->assertSame('test', $menu->getCode());

        $this->assertTrue($menu->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[1]->isAllowed());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[2]->isAllowed());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[3]->isAllowed());

        Item::resetAll();
    }

    public function testServiceAllowedConnectedWithoutRole()
    {
        Item::resetAll();

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->atLeastOnce())
            ->method('isGranted')
            ->will(
                $this->returnValueMap(
                    [
                        ['IS_AUTHENTICATED_REMEMBERED', null, true],
                        ['ROLE_TEST', null, false],
                    ]
                )
            );

        $definition = new MenuDefinition();

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $service = new Manager($authorizationChecker, $definition);
        $menu = $service->buildMenu('child_1_1');

        $this->assertSame('test', $menu->getCode());

        $this->assertTrue($menu->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[0]->isAllowed());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[2]->isAllowed());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[3]->isAllowed());

        Item::resetAll();
    }

    public function testServiceAllowedConnectedWithRole()
    {
        Item::resetAll();

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects($this->atLeastOnce())
            ->method('isGranted')
            ->will(
                $this->returnValueMap(
                    [
                        ['IS_AUTHENTICATED_REMEMBERED', null, true],
                        ['ROLE_TEST', null, true],
                    ]
                )
            );

        $definition = new MenuDefinition();

        /** @var AuthorizationCheckerInterface $authorizationChecker */
        $service = new Manager($authorizationChecker, $definition);
        $menu = $service->buildMenu('child_1_1');

        $this->assertSame('test', $menu->getCode());

        $this->assertTrue($menu->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[0]->isAllowed());
        $this->assertTrue($menu->getChildItems()[0]->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[0]->isAllowed());
        $this->assertFalse($menu->getChildItems()[1]->getChildItems()[1]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[2]->isAllowed());
        $this->assertTrue($menu->getChildItems()[1]->getChildItems()[3]->isAllowed());

        Item::resetAll();
    }
}

class MenuDefinition implements DefinitionInterface
{
    /**
     * @var Item
     */
    private $mainItem;

    /**
     * @return Item
     */
    public function getDefinition(): Item
    {
        $this->mainItem = new Item('Test', 'test', 'app_test');

        $this->mainItem
            ->addChild('Main 1', 'main_1')
                ->addChild('Child 1.1', 'child_1_1', 'app_child_1_1')->getParentItem()
                ->addChild('Child 1.2', 'child_1_2', 'app_child_1_2')->getParentItem()
            ->getParentItem()
            ->addChild('Main 2', 'main_2')
                ->addChild('Child 2.1', 'child_2_1', 'app_child_2_1')->getParentItem()
                ->addChild('Child 2.2', 'child_2_2', 'app_child_2_2')->setACL(false)->getParentItem()
                ->addChild('Child 2.3', 'child_2_3', 'app_child_2_3')->setACL(true)->getParentItem()
                ->addChild('Child 2.4', 'child_2_4', 'app_child_2_4')->setACL(true, 'ROLE_TEST')->getParentItem()
            ->getParentItem()
        ;
        
        return $this->mainItem;
    }
}
