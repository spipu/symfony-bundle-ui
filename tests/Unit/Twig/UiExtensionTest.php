<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Twig;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Menu\Item;
use Spipu\UiBundle\Service\Menu\Manager as MenuManager;
use Spipu\UiBundle\Service\Ui\UiManagerInterface;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Twig\UiExtension;
use Twig\Extension\ExtensionInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UiExtension::class)]
class UiExtensionTest extends TestCase
{
    public function getExtension(): UiExtension
    {
        $menuManagerMock = $this->createMock(MenuManager::class);

        $menuManagerMock->expects($this->any())
            ->method('buildMenu')
            ->willReturnCallback(
                    function (string $currentItemCode = '') {
                        $item = new Item($currentItemCode, $currentItemCode);
                        $item->setActive(true);
                        return $item;
                    }
            );

        /** @var MenuManager $menuManagerMock */
        $extension = new UiExtension(
            $menuManagerMock,
            SymfonyMock::getTranslator($this),
            SpipuUiMock::getPersonalDataProtection($this)
        );

        return $extension;
    }

    public function testExtension(): void
    {
        $extension = $this->getExtension();
        $this->assertTrue($extension instanceof ExtensionInterface);
    }

    public function testFunctions(): void
    {
        $allowedNames = [
            'renderManager',
            'getMenu',
            'getTranslations',
            'isClosure',
            'executeClosure',
            'is_personal_data_hidden_entity',
            'is_personal_data_hidden_field',
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

    public function testGetMenu(): void
    {
        Item::resetAll();

        $extension = $this->getExtension();
        $item = $extension->getMenu('test');

        $this->assertSame(1, $item->getId());
        $this->assertSame('test', $item->getCode());
        $this->assertSame(true, $item->isActive());

        Item::resetAll();
    }

    public function testIsPersonalDataHiddenEntity(): void
    {
        $extension = $this->getExtension();

        $entity = SpipuUiMock::getPersonalDataResourceMock();
        $this->assertFalse($extension->isPersonalDataHiddenEntity($entity));

        $entity = clone $entity;
        $entity->setHidden(true);
        $this->assertTrue($extension->isPersonalDataHiddenEntity($entity));

        $this->assertFalse($extension->isPersonalDataHiddenEntity(SpipuUiMock::getResourceMock()));
    }

    public function testIsPersonalDataHiddenField(): void
    {
        $extension = $this->getExtension();

        $entity = SpipuUiMock::getPersonalDataResourceMock();
        $entity->setFieldAB('secret');
        $this->assertFalse($extension->isPersonalDataHiddenField($entity, 'fieldAB'));

        $entity = clone $entity;
        $entity->setHidden(true);
        $this->assertTrue($extension->isPersonalDataHiddenField($entity, 'fieldAB'));
        $this->assertFalse($extension->isPersonalDataHiddenField($entity, 'fieldAA'));

        $resource = SpipuUiMock::getResourceMock()->setFieldAB('public');
        $this->assertFalse($extension->isPersonalDataHiddenField($resource, 'fieldAB'));
    }

    public function testRenderManager(): void
    {
        $mockManager = $this->createMock(UiManagerInterface::class);
        $mockManager
            ->expects($this->once())
            ->method('display')
            ->willReturnCallback(
                    function () {
                        return 'test';
                    }
            );

        $extension = $this->getExtension();

        ob_start();
        $extension->renderManager($mockManager);
        $result = ob_get_clean();

        $this->assertSame('test', $result);
    }
}
