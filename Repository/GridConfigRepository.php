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

    public function resetDefaults(): void
    {
        $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->delete('SpipuUiBundle:GridConfig', 'c')
            ->where('c.name = :name')
            ->setParameter('name', GridConfig::DEFAULT_NAME)
            ->getQuery()->execute();
    }
}
