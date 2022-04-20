<?php

namespace Spipu\UiBundle\Tests\Unit\Service\Ui\Grid;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Entity\GridConfig as GridConfigEntity;
use Spipu\UiBundle\Repository\GridConfigRepository;
use Spipu\UiBundle\Service\Ui\Grid\GridConfig;
use Spipu\UiBundle\Service\Ui\Grid\GridIdentifier;
use Spipu\UiBundle\Service\Ui\Grid\UserIdentifier;

class GridConfigTest extends TestCase
{
    /**
     * @param TestCase $testCase
     * @return GridConfig
     */
    static public function getService(TestCase $testCase): GridConfig
    {
        $security = SymfonyMock::getSecurity($testCase);
        $gridConfigRepository = $testCase->createMock(GridConfigRepository::class);

        $gridConfigRepository
            ->expects($testCase::any())
            ->method('getUserConfigById')
            ->willReturnCallback(
                function (string $gridIdentifier, string $userIdentifier, int $gridConfigId)
                {
                    $entity = new GridConfigEntity();
                    $entity->setGridIdentifier($gridIdentifier);
                    $entity->setUserIdentifier($userIdentifier);

                    return $entity;
                }
            );

        $gridConfigRepository
            ->expects($testCase::any())
            ->method('getUserConfigByName')
            ->willReturnCallback(
                function (string $gridIdentifier, string $userIdentifier, string $name)
                {
                    return null;
                }
            );

        $gridConfigRepository
            ->expects($testCase::any())
            ->method('getUserConfigs')
            ->willReturnCallback(
                function (string $gridIdentifier, string $userIdentifier)
                {
                    return [];
                }
            );

        $translator = SymfonyMock::getTranslator($testCase);

        $gridIdentifier = new GridIdentifier(SymfonyMock::getRequestStack($testCase));
        $userIdentifier = new UserIdentifier();

        return new GridConfig(
            $security,
            $gridConfigRepository,
            $translator,
            $gridIdentifier,
            $userIdentifier
        );
    }

    public function testService()
    {
        $service = self::getService($this);

        $gridDefinition = new Grid('test');
        $entity = $service->getUserConfig($gridDefinition, 1);

        $this->assertSame('fake_route/test', $entity->getGridIdentifier());
        $this->assertSame(md5('42'), $entity->getUserIdentifier());
    }
}
