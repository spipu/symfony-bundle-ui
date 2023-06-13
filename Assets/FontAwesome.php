<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UiBundle\Assets;

use Spipu\CoreBundle\Assets\AssetInterface;

/**
 * FontAwesome asset
 * https://fontawesome.com/changelog/latest
 */
class FontAwesome implements AssetInterface
{
    private string $version;

    public function __construct(string $version = '5.15.4')
    {
        $this->version = $version;
    }

    public function getCode(): string
    {
        return 'fontawesome';
    }

    public function getSourceType(): string
    {
        return self::TYPE_URL_ZIP;
    }

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
