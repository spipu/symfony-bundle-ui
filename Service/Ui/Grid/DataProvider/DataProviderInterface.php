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

namespace Spipu\UiBundle\Service\Ui\Grid\DataProvider;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;

interface DataProviderInterface
{
    public function setGridRequest(GridRequest $request): void;

    public function setGridDefinition(GridDefinition $definition): void;

    public function getNbTotalRows(): int;

    /**
     * @return EntityInterface[]
     */
    public function getPageRows(): array;
}
