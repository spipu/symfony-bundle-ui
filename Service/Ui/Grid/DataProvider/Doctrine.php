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

use Doctrine\ORM\Query\Expr\Andx;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\Column;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Spipu\UiBundle\Exception\GridException;

class Doctrine extends AbstractDataProvider
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $mappingValues = [];

    /**
     * Doctrine constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     */
    public function resetDataProvider(): void
    {
        parent::resetDataProvider();

        $this->conditions = [];
        $this->mappingValues = [];
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

        $queryBuilder = $this->entityManager->createQueryBuilder();

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
    protected function prepareQueryBuilderFilter(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        $value
    ): array {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = $this->getFieldFromColumn($column);

        if ($column->getFilter()->isRange()) {
            if (is_array($value) && array_key_exists('from', $value)) {
                $where->add($queryBuilder->expr()->gte($entityField, ':' . $code . '_from'));
                $parameters[':' . $code . '_from'] = $this->applyMappingValue($code, $value['from']);
            }
            if (is_array($value) && array_key_exists('to', $value)) {
                $where->add($queryBuilder->expr()->lte($entityField, ':' . $code . '_to'));
                $parameters[':' . $code . '_to'] = $this->applyMappingValue($code, $value['to']);
            }
            return $parameters;
        }

        if ($column->getFilter()->isExactValue() || $column->getType()->getType() == ColumnType::TYPE_SELECT) {
            $value = $this->applyMappingValue($code, $value);
            $expression = $queryBuilder->expr()->eq($entityField, ':' . $code);
            if (is_array($value)) {
                $expression = $queryBuilder->expr()->in($entityField, ':' . $code);
            }
            $where->add($expression);
            $parameters[':' . $code] = $value;
            return $parameters;
        }

        $where->add($queryBuilder->expr()->like($entityField, ':' . $code));
        $parameters[':' . $code] = '%' . $value . '%';

        return $parameters;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Andx $where
     * @param string $code
     * @param mixed $value
     * @return array
     */
    protected function prepareQueryBuilderQuickSearch(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        $value
    ): array {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = $this->getFieldFromColumn($column);

        $where->add($queryBuilder->expr()->like($entityField, ':' . $code));
        $parameters[':' . $code] = $value . '%';

        return $parameters;
    }

    /**
     * @return int
     * @throws GridException
     */
    public function getNbTotalRows(): int
    {
        $queryBuilder = $this->prepareQueryBuilder();
        $queryBuilder->select($queryBuilder->expr()->count('main'));

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return EntityInterface[]
     * @throws GridException
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
                $this->getFieldFromColumn($this->definition->getColumn($this->request->getSortColumn())),
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
    protected function applyMappingValue(string $fieldCode, $originalValue)
    {
        if (!array_key_exists($fieldCode, $this->mappingValues)) {
            return $originalValue;
        }

        if (!array_key_exists($originalValue, $this->mappingValues[$fieldCode])) {
            return $originalValue;
        }

        return $this->mappingValues[$fieldCode][$originalValue];
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function getFieldFromColumn(Column $column): string
    {
        $prefix = '';
        if (strpos($column->getEntityField(), '.') === false) {
            $prefix = 'main.';
        }

        return $prefix . $column->getEntityField();
    }
}
