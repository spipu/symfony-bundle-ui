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

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Andx;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Spipu\UiBundle\Exception\GridException;
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
     * @var array
     */
    private $mappingValues = [];

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
     * @param string $fieldCode
     * @param mixed $originalValue
     * @param mixed $newValue
     * @return void
     */
    public function addMappingValue(string $fieldCode, $originalValue, $newValue): void
    {
        if (!array_key_exists($fieldCode, $this->mappingValues)) {
            $this->mappingValues[$fieldCode] = [];
        }
        $this->mappingValues[$fieldCode][$originalValue] = $newValue;
    }

    /**
     * @return QueryBuilder
     * @throws GridException
     */
    public function prepareQueryBuilder(): QueryBuilder
    {
        $this->validate();

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
        foreach ($this->getFilters() as $code => $value) {
            $parameters += $this->prepareQueryBuilderFilter($queryBuilder, $where, $code, $value);
        }

        if ($this->request && $this->request->getQuickSearchField() && $this->request->getQuickSearchValue()) {
            $parameters += $this->prepareQueryBuilderQuickSearch(
                $queryBuilder,
                $where,
                $this->request->getQuickSearchField(),
                $this->request->getQuickSearchValue()
            );
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
    private function prepareQueryBuilderFilter(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        $value
    ): array {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = 'main.'.$column->getEntityField();

        if ($column->getFilter()->isRange()) {
            if (is_array($value) && array_key_exists('from', $value)) {
                $where->add($queryBuilder->expr()->gte($entityField, ':'.$code.'_from'));
                $parameters[':'.$code.'_from'] = $this->applyMappingValue($code, $value['from']);
            }
            if (is_array($value) && array_key_exists('to', $value)) {
                $where->add($queryBuilder->expr()->lte($entityField, ':'.$code.'_to'));
                $parameters[':'.$code.'_to'] = $this->applyMappingValue($code, $value['to']);
            }
            return $parameters;
        }

        if ($column->getFilter()->isExactValue() || $column->getType()->getType() == ColumnType::TYPE_SELECT) {
            $value = $this->applyMappingValue($code, $value);
            $expression = $queryBuilder->expr()->eq($entityField, ':'.$code);
            if (is_array($value)) {
                $expression = $queryBuilder->expr()->in($entityField, ':'.$code);
            }
            $where->add($expression);
            $parameters[':'.$code] = $value;
            return $parameters;
        }

        $where->add($queryBuilder->expr()->like($entityField, ':'.$code));
        $parameters[':'.$code] = '%'.$value.'%';

        return $parameters;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Andx $where
     * @param string $code
     * @param mixed $value
     * @return array
     */
    private function prepareQueryBuilderQuickSearch(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        $value
    ): array {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = 'main.'.$column->getEntityField();

        $where->add($queryBuilder->expr()->like($entityField, ':'.$code));
        $parameters[':'.$code] = $value.'%';

        return $parameters;
    }

    /**
     * @return int
     */
    public function getNbTotalRows(): int
    {
        $queryBuilder = $this->prepareQueryBuilder();
        $queryBuilder->select($queryBuilder->expr()->count('main'));

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return EntityInterface[]
     */
    public function getPageRows(): array
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

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string $fieldCode
     * @param mixed $originalValue
     * @return mixed
     */
    private function applyMappingValue(string $fieldCode, $originalValue)
    {
        if (!array_key_exists($fieldCode, $this->mappingValues)) {
            return $originalValue;
        }

        if (!array_key_exists($originalValue, $this->mappingValues[$fieldCode])) {
            return $originalValue;
        }

        return $this->mappingValues[$fieldCode][$originalValue];
    }
}
