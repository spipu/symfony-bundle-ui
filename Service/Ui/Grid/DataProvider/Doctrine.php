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

use Doctrine\ORM\Query\Expr\Andx;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Doctrine extends AbstractDataProvider
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $conditions = [];

    /**
     * Doctrine constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param mixed $condition
     * @return void
     */
    public function addCondition($condition): void
    {
        $this->conditions[] = $condition;
    }

    /**
     * @return QueryBuilder
     */
    private function prepareQueryBuilder(): QueryBuilder
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('main')
            ->from($this->definition->getEntityName(), 'main');

        $where = $queryBuilder->expr()->andX();
        foreach ($this->conditions as $condition) {
            $where->add($condition);
        }

        $parameters = [];
        foreach ($this->request->getFilters() as $code => $value) {
            $parameters += $this->prepareQueryBuilderFilter($queryBuilder, $where, $code, $value);
        }

        if (count($parameters) > 0 || count($this->conditions) > 0) {
            $queryBuilder->where($where);
            $queryBuilder->setParameters($parameters);
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Andx $where
     * @param string $code
     * @param mixed $value
     * @return array
     */
    private function prepareQueryBuilderFilter(QueryBuilder $queryBuilder, Andx $where, string $code, $value): array
    {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = 'main.'.$column->getEntityField();

        if ($column->getFilter()->isRange()) {
            if (!is_array($value)) {
                return $parameters;
            }
            if (array_key_exists('from', $value)) {
                $where->add($queryBuilder->expr()->gte($entityField, ':'.$code.'_from'));
                $parameters[':'.$code.'_from'] = $value['from'];
            }
            if (array_key_exists('to', $value)) {
                $where->add($queryBuilder->expr()->lte($entityField, ':'.$code.'_to'));
                $parameters[':'.$code.'_to'] = $value['to'];
            }
            return $parameters;
        }

        if ($column->getType()->getType() == ColumnType::TYPE_SELECT) {
            $where->add($queryBuilder->expr()->eq($entityField, ':'.$code));
            $parameters[':'.$code] = $value;
            return $parameters;
        }

        $where->add($queryBuilder->expr()->like($entityField, ':'.$code));
        $parameters[':'.$code] = '%'.$value.'%';

        return $parameters;
    }

    /**
     * @return Query
     */
    private function prepareCountQuery(): Query
    {
        $queryBuilder = $this->prepareQueryBuilder();

        $queryBuilder->select($queryBuilder->expr()->count('main'));

        return $queryBuilder->getQuery();
    }

    /**
     * @return Query
     */
    private function prepareRowQuery(): Query
    {
        $queryBuilder = $this->prepareQueryBuilder();

        if ($this->request->getPageLength()) {
            $queryBuilder->setMaxResults($this->request->getPageLength());
        }

        if ($this->request->getPageCurrent()) {
            $queryBuilder->setFirstResult(($this->request->getPageCurrent() - 1) * $this->request->getPageLength());
        }

        if ($this->request->getSortColumn()) {
            $queryBuilder->orderBy(
                'main.'.$this->definition->getColumn($this->request->getSortColumn())->getEntityField(),
                $this->request->getSortOrder()
            );
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @return int
     */
    public function getNbTotalRows(): int
    {
        try {
            return (int) $this->prepareCountQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @return EntityInterface[]
     */
    public function getPageRows(): array
    {
        return $this->prepareRowQuery()->execute();
    }
}
