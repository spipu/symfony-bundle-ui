<?php
namespace Spipu\UiBundle\Tests\Unit\Assets;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Assets\AssetInterface;
use Spipu\UiBundle\Assets\Bootstrap;
use Spipu\UiBundle\Assets\FontAwesome;
use Spipu\UiBundle\Assets\Jquery;
use Spipu\UiBundle\Assets\Popper;
use Spipu\UiBundle\Tests\SpipuUiMock;

class AssetsTest extends TestCase
{
    private $availableTypes = [AssetInterface::TYPE_URL, AssetInterface::TYPE_VENDOR, AssetInterface::TYPE_URL_ZIP];

    public function testBootstrap()
    {
        $this->assetTest(new Bootstrap(), 'bootstrap');
        $this->assetTest(new FontAwesome(), 'fontawesome');
        $this->assetTest(new Jquery(), 'jquery');
        $this->assetTest(new Popper(), 'popper');
    }

    private function assetTest($asset, string $code)
    {
        $this->assertInstanceOf(AssetInterface::class, $asset);

        /** @var AssetInterface $asset */
        $this->assertSame($code, $asset->getCode());
        $this->assertTrue(in_array($asset->getSourceType(), $this->availableTypes));

        $source = $asset->getSource();
        $mapping = $asset->getMapping();

        $this->assertIsString($source);

        $this->assertIsArray($mapping);
        foreach ($mapping as $from => $to) {
            $this->assertIsString($from);
            $this->assertIsString($to);
        }
    }
}
