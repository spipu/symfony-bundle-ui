<?php
namespace Spipu\UiBundle\Tests\Unit\Entity\Menu;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Menu\Item;

class ItemTest extends TestCase
{
    public function testEntity()
    {
        Item::resetAll();

        $main = new Item('main_name', 'main_code', 'main_route');

        $this->assertSame(1, $main->getId());
        $this->assertSame('main_name', $main->getName());
        $this->assertSame('main_code', $main->getCode());
        $this->assertSame('main_route', $main->getRoute());
        $this->assertSame([], $main->getRouteParams());
        $this->assertSame(null, $main->getIcon());
        $this->assertSame(null, $main->getIconThemeColor());
        $this->assertSame(null, $main->getIconTitle());
        $this->assertSame(null, $main->getConnected());
        $this->assertSame(null, $main->getRole());
        $this->assertSame(null, $main->getParentItem());
        $this->assertSame([], $main->getChildItems());
        $this->assertSame(false, $main->isActive());
        $this->assertSame(false, $main->isAllowed());

        $main
            ->setIcon('main_icon')
            ->setActive(true)
            ->setAllowed(true)
        ;

        $this->assertSame('main_icon', $main->getIcon());
        $this->assertSame('secondary', $main->getIconThemeColor());
        $this->assertSame(null, $main->getIconTitle());
        $this->assertSame(true, $main->isActive());
        $this->assertSame(true, $main->isAllowed());
        $this->assertSame(null, $main->getCssClass());

        $main->setIcon('main_icon', 'primary', 'icon');
        $this->assertSame('main_icon', $main->getIcon());
        $this->assertSame('primary', $main->getIconThemeColor());
        $this->assertSame('icon', $main->getIconTitle());

        $main->setCssClass('my-class');
        $this->assertSame('my-class', $main->getCssClass());

        $main->setACL(false);
        $this->assertSame(false, $main->getConnected());
        $this->assertSame(null, $main->getRole());

        $main->setACL(true);
        $this->assertSame(true, $main->getConnected());
        $this->assertSame(null, $main->getRole());

        $main->setACL(false, 'good.acl.1');
        $this->assertSame(true, $main->getConnected());
        $this->assertSame('good.acl.1', $main->getRole());

        $main->setACL(true, 'good.acl.2');
        $this->assertSame(true, $main->getConnected());
        $this->assertSame('good.acl.2', $main->getRole());

        $child = $main->addChild('child_name', 'child_code', 'child_route');
        $this->assertSame(2, $child->getId());
        $this->assertSame('child_name', $child->getName());
        $this->assertSame('child_code', $child->getCode());
        $this->assertSame('child_route', $child->getRoute());
        $this->assertSame([], $child->getRouteParams());
        $this->assertSame($main, $child->getParentItem());
        $this->assertSame([$child], $main->getChildItems());

        $otherChild = $main->addChild('child_name2', 'child_code2', 'child_route2', ['foo' => 'bar']);
        $this->assertSame(3, $otherChild->getId());
        $this->assertSame('child_name2', $otherChild->getName());
        $this->assertSame('child_code2', $otherChild->getCode());
        $this->assertSame('child_route2', $otherChild->getRoute());
        $this->assertSame(['foo' => 'bar'], $otherChild->getRouteParams());
        $this->assertSame($main, $child->getParentItem());
        $this->assertSame([$child, $otherChild], $main->getChildItems());

        Item::resetAll();
    }
}
