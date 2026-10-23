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

use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Andx;
use SortDirection;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\Column;
use Spipu\UiBundle\Entity\Grid\ColumnType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Service\Ui\PersonalDataProtection;
use Stringable;

class Doctrine extends AbstractDataProvider
{
    protected EntityManagerInterface $entityManager;
    protected PersonalDataProtection $personalDataProtection;
    protected array $conditions = [];
    protected array $mappingValues = [];
    protected array $joins = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        PersonalDataProtection $personalDataProtection
    ) {
        $this->entityManager = $entityManager;
        $this->personalDataProtection = $personalDataProtection;
    }

    public function resetDataProvider(): void
    {
        parent::resetDataProvider();

        $this->conditions = [];
        $this->mappingValues = [];
        $this->joins = [];
    }

    public function addCondition(mixed $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function addMappingValue(string $fieldCode, mixed $originalValue, mixed $newValue): void
    {
        if (!array_key_exists($fieldCode, $this->mappingValues)) {
            $this->mappingValues[$fieldCode] = [];
        }
        $this->mappingValues[$fieldCode][$originalValue] = $newValue;
    }

    public function addJoin(string $columnName, string $joinType = 'inner'): void
    {
        if (!in_array($joinType, ['inner', 'left'])) {
            throw new GridException('Unknown join type');
        }

        $this->joins[$columnName] = $joinType;
    }

    /**
     * @SuppressWarnings(PMD.CyclomaticComplexity)
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

        foreach ($this->joins as $columnName => $joinType) {
            $queryBuilder->{"{$joinType}Join"}('main.' . $columnName, $columnName);
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
            foreach ($parameters as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }

        return $queryBuilder;
    }

    protected function prepareQueryBuilderFilter(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        mixed $value
    ): array {
        $column = $this->definition->getColumn($code);

        $parameters = $this->prepareQueryBuilderFilterValue($queryBuilder, $where, $column, $code, $value);
        if (count($parameters) > 0) {
            $parameters += $this->preparePersonalDataCondition($queryBuilder, $where, $column);
        }

        return $parameters;
    }

    protected function prepareQueryBuilderFilterValue(
        QueryBuilder $queryBuilder,
        Andx $where,
        Column $column,
        string $code,
        mixed $value
    ): array {
        $parameters = [];

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

        $value = $this->applyValueTransformer($column, (string) $value);
        $where->add($queryBuilder->expr()->like($entityField, ':' . $code));
        $parameters[':' . $code] = '%' . $value . '%';

        return $parameters;
    }

    protected function prepareQueryBuilderQuickSearch(
        QueryBuilder $queryBuilder,
        Andx $where,
        string $code,
        ?string $value
    ): array {
        $parameters = [];

        $column = $this->definition->getColumn($code);
        $entityField = $this->getFieldFromColumn($column);

        $value = $this->applyValueTransformer($column, $value);
        $where->add($queryBuilder->expr()->like($entityField, ':' . $code));
        $parameters[':' . $code] = $value . '%';
        $parameters += $this->preparePersonalDataCondition($queryBuilder, $where, $column);

        return $parameters;
    }

    protected function preparePersonalDataCondition(QueryBuilder $queryBuilder, Andx $where, Column $column): array
    {
        $entityName = $this->definition->getEntityName();
        if (!$this->personalDataProtection->isProtectedField($entityName, $column->getEntityField())) {
            return [];
        }

        $parameterName = $this->getPersonalDataParameterName();
        $dateField = 'main.' . $this->personalDataProtection->getHiddenAtField($entityName);
        $where->add(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull($dateField),
                $queryBuilder->expr()->gt($dateField, $parameterName)
            )
        );

        return [$parameterName => new DateTimeImmutable()];
    }

    protected function getPersonalDataParameterName(): string
    {
        $reserved = [];
        foreach ($this->definition->getColumns() as $column) {
            $code = $column->getCode();
            $reserved[] = ':' . $code;
            $reserved[] = ':' . $code . '_from';
            $reserved[] = ':' . $code . '_to';
        }

        foreach ($this->conditions as $condition) {
            if (is_string($condition) || ($condition instanceof Stringable)) {
                preg_match_all('/:[a-z_][a-z0-9_]*/i', (string) $condition, $matches);
                $reserved = array_merge($reserved, $matches[0]);
            }
        }

        $name = ':personal_data_now';
        while (in_array($name, $reserved, true)) {
            $name .= '_';
        }

        return $name;
    }

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
            $sortOrder = SortDirection::Ascending;
            if ($this->request->getSortOrder() === 'desc') {
                $sortOrder = SortDirection::Descending;
            }
            $queryBuilder->orderBy(
                $this->getFieldFromColumn($this->definition->getColumn($this->request->getSortColumn())),
                $sortOrder
            );
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function applyMappingValue(string $fieldCode, mixed $originalValue): mixed
    {
        if (!array_key_exists($fieldCode, $this->mappingValues)) {
            return $originalValue;
        }

        if (!array_key_exists($originalValue, $this->mappingValues[$fieldCode])) {
            return $originalValue;
        }

        return $this->mappingValues[$fieldCode][$originalValue];
    }

    protected function getFieldFromColumn(Column $column): string
    {
        $prefix = '';
        if (!str_contains($column->getEntityField(), '.')) {
            $prefix = 'main.';
        }

        return $prefix . $column->getEntityField();
    }
}
