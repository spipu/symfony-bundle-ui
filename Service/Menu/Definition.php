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

namespace Spipu\UiBundle\Service\Menu;

use Spipu\UiBundle\Entity\Menu\Item;

/**
 * Class Definition - Example. Make your own menu definition !
 */
class Definition implements DefinitionInterface
{
    /**
     * @var Item
     */
    private $mainItem;

    /**
     * @return void
     */
    private function build(): void
    {
        $this->mainItem = new Item('Main');
    }

    /**
     * @return Item
     */
    public function getDefinition(): Item
    {
        if (!$this->mainItem) {
            $this->build();
        }

        return $this->mainItem;
    }
}
