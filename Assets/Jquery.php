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
class Jquery implements AssetInterface
{
    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'jquery';
    }

    /**
     * @return string
     */
    public function getSourceType(): string
    {
        return self::TYPE_VENDOR;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'components/jquery';
    }

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return [
            'jquery.js' => 'js/jquery.js',
        ];
    }
}
