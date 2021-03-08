<?php
namespace Spipu\UiBundle\Tests\Unit\Twig;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Tests\SpipuUiMock;
use Spipu\UiBundle\Twig\OptionsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Error\Error;
use Twig\Extension\ExtensionInterface;

class OptionsExtensionTest extends TestCase
{
    public function getExtension(): OptionsExtension
    {
        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['@GoodOption', 1, SpipuUiMock::getOptionIntegerMock()],
                        ['@WrongOption', 1, new \stdClass()],
                    ]
                )
            );

        /** @var ContainerInterface $mockContainer */
        $extension = new OptionsExtension($mockContainer);

        return $extension;
    }

    public function testExtension()
    {
        $extension = $this->getExtension();
        $this->assertTrue($extension instanceof ExtensionInterface);
    }

    public function testFilters()
    {
        $allowedNames = [
            'label_from_option',
            'label_from_option_name',
        ];

        $extension = $this->getExtension();
        $filters = $extension->getFilters();

        $foundNames = [];
        foreach ($filters as $filter) {
            $this->assertTrue(in_array($filter->getName(), $allowedNames));
            $this->assertTrue(call_user_func_array('method_exists', $filter->getCallable()));
            $foundNames[] = $filter->getName();
        }

        $this->assertSame(count($allowedNames), count($foundNames));
    }

    public function testLabelFromOption()
    {
        $GoodOption = SpipuUiMock::getOptionIntegerMock();

        $extension = $this->getExtension();

        $this->assertSame('no', $extension->getLabelFromOption('0', $GoodOption));
        $this->assertSame('yes', $extension->getLabelFromOption('1', $GoodOption));

        $this->assertSame('no', $extension->getLabelFromOption(0, $GoodOption));
        $this->assertSame('yes', $extension->getLabelFromOption(1, $GoodOption));

        $this->assertSame('no', $extension->getLabelFromOption(false, $GoodOption));
        $this->assertSame('yes', $extension->getLabelFromOption(true, $GoodOption));

        $object = $this->createMock(EntityInterface::class);
        $object->expects($this->once())->method('getId')->willReturn(0);
        $this->assertSame('no', $extension->getLabelFromOption($object, $GoodOption));

        $object = $this->createMock(EntityInterface::class);
        $object->expects($this->once())->method('getId')->willReturn(1);
        $this->assertSame('yes', $extension->getLabelFromOption($object, $GoodOption));
    }

    public function testLabelFromOptionNameOk()
    {
        $serviceName = '@GoodOption';

        $extension = $this->getExtension();

        $this->assertSame('no', $extension->getLabelFromOptionName('0', $serviceName));
        $this->assertSame('yes', $extension->getLabelFromOptionName('1', $serviceName));

        $this->assertSame('no', $extension->getLabelFromOptionName(0, $serviceName));
        $this->assertSame('yes', $extension->getLabelFromOptionName(1, $serviceName));

        $this->assertSame('no', $extension->getLabelFromOptionName(false, $serviceName));
        $this->assertSame('yes', $extension->getLabelFromOptionName(true, $serviceName));
    }

    public function testLabelFromOptionNameKo()
    {
        $serviceName = '@WrongOption';

        $extension = $this->getExtension();

        $this->expectException(Error::class);
        $extension->getLabelFromOptionName('value', $serviceName);
    }
}

