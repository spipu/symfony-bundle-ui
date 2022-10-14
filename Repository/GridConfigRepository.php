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

namespace Spipu\UiBundle\Repository;

use Spipu\UiBundle\Entity\GridConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GridConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method GridConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method GridConfig[]    findAll()
 * @method GridConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GridConfigRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GridConfig::class);
    }

    /**
     * @param string $gridIdentifier
     * @param string $userIdentifier
     * @return GridConfig[]
     */
    public function getUserConfigs(string $gridIdentifier, string $userIdentifier): array
    {
        return $this->findBy(
            [
                'gridIdentifier' => $gridIdentifier,
                'userIdentifier' => $userIdentifier,
            ],
            [
                'name' => 'ASC'
            ]
        );
    }

    /**
     * @param string $gridIdentifier
     * @param string $userIdentifier
     * @param int $gridConfigId
     * @return GridConfig|null
     */
    public function getUserConfigById(string $gridIdentifier, string $userIdentifier, int $gridConfigId): ?GridConfig
    {
        return $this->findOneBy(
            [
                'gridIdentifier' => $gridIdentifier,
                'userIdentifier' => $userIdentifier,
                'id' => $gridConfigId,
            ]
        );
    }

    /**
     * @param string $gridIdentifier
     * @param string $userIdentifier
     * @param string $name
     * @return GridConfig|null
     */
    public function getUserConfigByName(string $gridIdentifier, string $userIdentifier, string $name): ?GridConfig
    {
        return $this->findOneBy(
            [
                'gridIdentifier' => $gridIdentifier,
                'userIdentifier' => $userIdentifier,
                'name' => $name,
            ]
        );
    }
}
