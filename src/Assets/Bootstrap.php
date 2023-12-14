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

class Bootstrap implements AssetInterface
{
    public function getCode(): string
    {
        return 'bootstrap';
    }

    public function getSourceType(): string
    {
        return self::TYPE_VENDOR;
    }

    public function getSource(): string
    {
        return 'twbs/bootstrap';
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'dist/css' => 'css',
            'dist/js'  => 'js',
        ];
    }
}
