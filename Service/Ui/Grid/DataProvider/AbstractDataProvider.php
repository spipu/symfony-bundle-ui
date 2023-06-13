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
    protected ?GridRequest $request = null;
    protected ?GridDefinition $definition = null;
    private ?array $filters = null;

    public function __clone()
    {
        $this->request = null;
        $this->definition = null;
        $this->filters = null;
    }

    public function setGridRequest(GridRequest $request): void
    {
        $this->request = $request;
    }

    public function setGridDefinition(GridDefinition $definition): void
    {
        $this->definition = $definition;
    }

    public function getRequest(): ?GridRequest
    {
        return $this->request;
    }

    public function getDefinition(): ?GridDefinition
    {
        return $this->definition;
    }

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

    public function resetDataProvider(): void
    {
        $this->filters = null;
    }

    public function forceFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getFilters(): array
    {
        $this->validate();

        if (is_array($this->filters)) {
            return $this->filters;
        }

        return $this->request->getFilters();
    }
}
