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

use Spipu\UiBundle\Exception\GridException;
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
     * @var array|null
     */
    private $filters = null;

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

    /**
     * @return GridRequest|null
     */
    public function getRequest(): ?GridRequest
    {
        return $this->request;
    }

    /**
     * @return GridDefinition|null
     */
    public function getDefinition(): ?GridDefinition
    {
        return $this->definition;
    }

    /**
     * @return bool
     * @throws GridException
     */
    public function validate(): bool
    {
        if ($this->definition === null) {
            throw new GridException('The data provider is not ready');
        }

        if ($this->request === null && $this->filters === null) {
            throw new GridException('The data provider is not ready');
        }

        return true;
    }

    /**
     * @param array $filters
     * @return void
     */
    public function forceFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return array
     * @throws GridException
     */
    public function getFilters(): array
    {
        $this->validate();

        if (is_array($this->filters)) {
            return $this->filters;
        }

        return $this->request->getFilters();
    }
}
