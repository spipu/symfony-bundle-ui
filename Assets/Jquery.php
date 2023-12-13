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
 * Jquery asset
 * https://jquery.com/download/
 */
class Jquery implements AssetInterface
{
    private string $version;

    public function __construct(string $version = '3.7.1')
    {
        $this->version = $version;
    }

    public function getCode(): string
    {
        return 'jquery';
    }

    public function getSourceType(): string
    {
        return self::TYPE_URL;
    }

    public function getSource(): string
    {
        return 'https://code.jquery.com/';
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'jquery-' . $this->version . '.js'          => 'js/jquery.js',
            'jquery-' . $this->version . '.min.js'      => 'js/jquery.min.js',
            'jquery-' . $this->version . '.slim.js'     => 'js/jquery.slim.js',
            'jquery-' . $this->version . '.slim.min.js' => 'js/jquery.slim.min.js',
        ];
    }
}
