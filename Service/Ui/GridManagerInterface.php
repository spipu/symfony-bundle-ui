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

namespace Spipu\UiBundle\Service\Ui;

use Spipu\UiBundle\Service\Ui\Grid\DataProvider\DataProviderInterface;

interface GridManagerInterface extends UiManagerInterface
{
    /**
     * @param string $name
     * @param array $parameters
     * @return GridManagerInterface
     */
    public function setRoute(string $name, array $parameters = []): GridManagerInterface;

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider(): DataProviderInterface;
}
