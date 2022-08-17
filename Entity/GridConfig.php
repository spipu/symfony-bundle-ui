<?php

namespace Spipu\UiBundle\Entity;

use Spipu\UiBundle\Repository\GridConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GridConfigRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *     name="spipu_ui_grid_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_GRID_CONFIG", columns={"grid_identifier", "user_identifier", "name"})
 *     }
 * )
 */
class GridConfig
{
    use TimestampableTrait;

    public const DEFAULT_NAME = 'default';

    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $gridIdentifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $userIdentifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $config = [];

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGridIdentifier(): string
    {
        return $this->gridIdentifier;
    }

    /**
     * @param string $gridIdentifier
     * @return $this
     */
    public function setGridIdentifier(string $gridIdentifier): self
    {
        $this->gridIdentifier = $gridIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    /**
     * @param string $userIdentifier
     * @return $this
     */
    public function setUserIdentifier(string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param int $length
     * @return string
     */
    public function getCutName(int $length): string
    {
        if (strlen($this->name) < $length - 3) {
            return $this->name;
        }

        return substr($this->name, 0, $length - 3) . '...';
    }

    /**
     * @return array
     */
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

    /**
     * @param string $key
     * @return string
     */
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

    /**
     * @return array
     */
    public function getConfigFilters(): array
    {
        if (!array_key_exists('filters', $this->config)) {
            return [];
        }

        return $this->config['filters'];
    }

    /**
     * @return string|null
     */
    public function getConfigSortColumn(): ?string
    {
        $sortConfig = $this->getConfigSort();
        if (!array_key_exists('column', $sortConfig)) {
            return null;
        }

        return $sortConfig['column'];
    }

    /**
     * @return string|null
     */
    public function getConfigSortOrder(): ?string
    {
        $sortConfig = $this->getConfigSort();
        if (!array_key_exists('order', $sortConfig)) {
            return null;
        }

        return $sortConfig['order'];
    }

    /**
     * @return array
     */
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
    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->name === self::DEFAULT_NAME;
    }
}
