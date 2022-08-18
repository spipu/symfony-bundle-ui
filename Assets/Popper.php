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
 * Popper asset
 * https://github.com/popperjs/popper-core/releases
 */
class Popper implements AssetInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'popper';
    }

    /**
     * @return string
     */
    public function getSourceType(): string
    {
        return self::TYPE_URL;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'https://unpkg.com/popper.js/dist/umd/';
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'popper.js'         => 'js/popper.js',
            'popper.min.js'     => 'js/popper.min.js',
            // 'popper.min.js.map' => 'js/popper.min.js.map',
        ];
    }
}
