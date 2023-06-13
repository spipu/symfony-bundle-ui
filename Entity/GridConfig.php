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

namespace Spipu\UiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'Spipu\UiBundle\Repository\GridConfigRepository')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "spipu_ui_grid_config")]
#[ORM\UniqueConstraint(name: "UNIQ_GRID_CONFIG", columns: ["grid_identifier", "user_identifier", "name"])]
class GridConfig
{
    use TimestampableTrait;

    public const DEFAULT_NAME = 'default';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $gridIdentifier = null;

    #[ORM\Column(length: 255)]
    private ?string $userIdentifier = null;

    #[ORM\Column(length: 64)]
    private ?string $name = null;

    #[ORM\Column(type: "json")]
    private array $config = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGridIdentifier(): string
    {
        return $this->gridIdentifier;
    }

    public function setGridIdentifier(string $gridIdentifier): self
    {
        $this->gridIdentifier = $gridIdentifier;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCutName(int $length): string
    {
        if (strlen($this->name) < $length - 3) {
            return $this->name;
        }

        return substr($this->name, 0, $length - 3) . '...';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return string[]
     */
    public function getConfigColumns(): array
    {
        if (!array_key_exists('columns', $this->config)) {
            return [];
        }

        return $this->config['columns'];
    }

    public function getConfigFilter(string $key): string
    {
        $values = $this->getConfigFilters();
        if (!array_key_exists($key, $values)) {
            return '';
        }

        $value = $values[$key];
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        return (string) $value;
    }

    public function getConfigFilters(): array
    {
        if (!array_key_exists('filters', $this->config)) {
            return [];
        }

        return $this->config['filters'];
    }

    public function getConfigSortColumn(): ?string
    {
        $sortConfig = $this->getConfigSort();
        if (!array_key_exists('column', $sortConfig)) {
            return null;
        }

        return $sortConfig['column'];
    }

    public function getConfigSortOrder(): ?string
    {
        $sortConfig = $this->getConfigSort();
        if (!array_key_exists('order', $sortConfig)) {
            return null;
        }

        return $sortConfig['order'];
    }

    public function getConfigSort(): array
    {
        if (!array_key_exists('sort', $this->config)) {
            return [
                'column' => null,
                'order'  => null,
            ];
        }

        return $this->config['sort'];
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->name === self::DEFAULT_NAME;
    }
}
