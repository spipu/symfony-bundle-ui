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
