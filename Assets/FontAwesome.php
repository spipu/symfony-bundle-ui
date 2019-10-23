<?php
/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Spipu\UiBundle\Assets;

use Spipu\CoreBundle\Assets\AssetInterface;

/**
 * All the assets
 */
class FontAwesome implements AssetInterface
{
    /**
     * @var string
     */
    private $version;

    /**
     * Jquery constructor.
     * @param string $version
     */
    public function __construct(string $version = '5.11.2')
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'fontawesome';
    }

    /**
     * @return string
     */
    public function getSourceType(): string
    {
        return self::TYPE_URL_ZIP;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'https://use.fontawesome.com/releases/v' . $this->version
            . '/fontawesome-free-' . $this->version . '-web.zip';
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'fontawesome-free-' . $this->version . '-web/css/all.css'     => 'css/all.css',
            'fontawesome-free-' . $this->version . '-web/css/all.min.css' => 'css/all.min.css',
            'fontawesome-free-' . $this->version . '-web/webfonts'        => 'webfonts',
        ];
    }
}
