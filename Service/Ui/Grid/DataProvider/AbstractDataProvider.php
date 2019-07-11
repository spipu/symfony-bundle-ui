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

namespace Spipu\UiBundle\Service\Ui\Grid\DataProvider;

use Spipu\UiBundle\Service\Ui\Grid\GridRequest;
use Spipu\UiBundle\Entity\Grid\Grid as GridDefinition;

abstract class AbstractDataProvider implements DataProviderInterface
{
    /**
     * @var GridRequest
     */
    protected $request;

    /**
     * @var GridDefinition
     */
    protected $definition;

    /**
     * need by Spipu Ui
     *
     * @return void
     */
    public function __clone()
    {
        $this->request = null;
        $this->definition = null;
    }

    /**
     * @param GridRequest $request
     * @return void
     */
    public function setGridRequest(GridRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * @param GridDefinition $definition
     * @return void
     */
    public function setGridDefinition(GridDefinition $definition): void
    {
        $this->definition = $definition;
    }
}
