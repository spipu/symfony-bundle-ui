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

namespace Spipu\UiBundle\Service\Ui\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Entity\GridConfig as GridConfigEntity;
use Spipu\UiBundle\Exception\UiException;
use Spipu\UiBundle\Repository\GridConfigRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PMD.ExcessiveClassComplexity)
 */
class GridConfig
{
    private Security $security;
    private GridConfigRepository $gridConfigRepository;
    private TranslatorInterface $translator;
    private GridIdentifierInterface $gridIdentifier;
    private UserIdentifierInterface $userIdentifier;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Security $security,
        GridConfigRepository $gridConfigRepository,
        TranslatorInterface $translator,
        GridIdentifierInterface $gridIdentifier,
        UserIdentifierInterface $userIdentifier,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->gridConfigRepository = $gridConfigRepository;
        $this->translator = $translator;
        $this->gridIdentifier = $gridIdentifier;
        $this->userIdentifier = $userIdentifier;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Grid $grid
     * @return GridConfigEntity[]
     */
    public function getUserConfigs(Grid $grid): array
    {
        $gridIdentifier = $this->getGridIdentifier($grid);
        $userIdentifier = $this->getUserIdentifier();

        $configs = $this->gridConfigRepository->getUserConfigs($gridIdentifier, $userIdentifier);

        $hasDefault = false;
        foreach ($configs as $config) {
            if ($config->isDefault()) {
                $hasDefault = true;
            }
        }

        if (!$hasDefault) {
            $configs[] = $this->getDefaultUserConfig($grid);
        }

        return $configs;
    }

    public function getUserConfig(Grid $grid, int $gridConfigId): ?GridConfigEntity
    {
        $gridIdentifier = $this->getGridIdentifier($grid);
        $userIdentifier = $this->getUserIdentifier();

        return $this->gridConfigRepository->getUserConfigById(
            $gridIdentifier,
            $userIdentifier,
            $gridConfigId
        );
    }

    public function getDefaultUserConfig(Grid $grid): GridConfigEntity
    {
        $gridIdentifier = $this->getGridIdentifier($grid);
        $userIdentifier = $this->getUserIdentifier();

        $gridConfig = $this->gridConfigRepository->getUserConfigByName(
            $gridIdentifier,
            $userIdentifier,
            GridConfigEntity::DEFAULT_NAME
        );

        if (!$gridConfig) {
            $gridConfig = $this->createUserConfig($grid, GridConfigEntity::DEFAULT_NAME);
        }

        return $gridConfig;
    }

    public function createUserConfig(Grid $grid, string $name): GridConfigEntity
    {
        $gridIdentifier = $this->getGridIdentifier($grid);
        $userIdentifier = $this->getUserIdentifier();

        $columns = [];
        foreach ($grid->getColumns() as $column) {
            if ($column->isDisplayed()) {
                $columns[$column->getCode()] = $column->getPosition();
            }
        }
        asort($columns);

        $config = [
            'columns' => array_keys($columns),
            'sort' => [
                'column' => $grid->getDefaultSortColumn(),
                'order'  => $grid->getDefaultSortOrder(),
            ],
            'filters' => [],
        ];

        $gridConfig = new GridConfigEntity();
        $gridConfig
            ->setGridIdentifier($gridIdentifier)
            ->setUserIdentifier($userIdentifier)
            ->setName(substr($name, 0, 64))
            ->setConfig($config)
        ;

        $this->entityManager->persist($gridConfig);
        $this->entityManager->flush();

        return $gridConfig;
    }

    private function getGridIdentifier(Grid $grid): string
    {
        return $this->gridIdentifier->getIdentifier($grid);
    }

    private function getUserIdentifier(): string
    {
        $user = $this->security->getUser();
        return $this->userIdentifier->getIdentifier($user);
    }

    public function getPersonalizeDefinition(Grid $grid, ?int $currentConfigId): array
    {
        $definition = [
            'columns' => [],
            'configs' => [],
        ];

        foreach ($grid->getColumns() as $column) {
            $definition['columns'][$column->getCode()] = [
                'code'      => $column->getCode(),
                'name'      => $this->translator->trans($column->getName()),
            ];
        }

        $defaultConfigId = null;
        $configs = $this->getUserConfigs($grid);
        foreach ($configs as $config) {
            $definition['configs'][$config->getId()] = $config;
            if ($config->isDefault()) {
                $defaultConfigId = $config->getId();
            }
        }
        if (!array_key_exists($currentConfigId, $definition['configs'])) {
            $currentConfigId = null;
        }
        $definition['current'] = $currentConfigId ?? $defaultConfigId;

        return $definition;
    }

    public function makeAction(Grid $grid, string $action, array $params): ?GridConfigEntity
    {
        $action = strip_tags($action);

        switch ($action) {
            case 'create':
                return $this->makeActionCreate($grid, $params);

            case 'select':
                return $this->makeActionSelect($grid, $params);

            case 'delete':
                return $this->makeActionDelete($grid, $params);

            case 'update':
                return $this->makeActionUpdate($grid, $params);

            default:
                throw new UiException('Unknown grid config action: ' . $action);
        }
    }

    private function makeActionCreate(Grid $grid, array $params): ?GridConfigEntity
    {
        if (!array_key_exists('name', $params) || !is_string($params['name'])) {
            throw new UiException('Name is missing');
        }

        $name = trim(strip_tags($params['name']));
        if ($name === '') {
            throw new UiException('Name is invalid');
        }

        return $this->createUserConfig($grid, $name);
    }

    private function makeActionSelect(Grid $grid, array $params): GridConfigEntity
    {
        if (!array_key_exists('id', $params) || !is_numeric($params['id'])) {
            throw new UiException('Id is invalid');
        }

        $config = $this->getUserConfig($grid, (int) $params['id']);
        if ($config === null) {
            throw new UiException('Id is unknown');
        }

        return $config;
    }

    private function makeActionDelete(Grid $grid, array $params): ?GridConfigEntity
    {
        $gridConfig = $this->makeActionSelect($grid, $params);
        if ($gridConfig->isDefault()) {
            throw new UiException('You can not delete the default display');
        }

        $this->entityManager->remove($gridConfig);
        $this->entityManager->flush();

        return $this->getDefaultUserConfig($grid);
    }

    private function makeActionUpdate(Grid $grid, array $params): ?GridConfigEntity
    {
        $gridConfig = $this->makeActionSelect($grid, $params);

        $config = [
            'columns' => $this->prepareUpdateColumns($params, $grid),
            'sort'    => $this->prepareUpdateSort($params, $grid),
            'filters' => $this->prepareUpdateFilters($params, $grid),
        ];

        $gridConfig->setConfig($config);

        $this->entityManager->flush();

        return $gridConfig;
    }

    protected function prepareUpdateColumns(array $params, Grid $grid): array
    {
        if (
            !array_key_exists('columns', $params)
            || !is_array($params['columns'])
        ) {
            throw new UiException('bad data');
        }

        $displayedColumns = [];
        foreach ($params['columns'] as $column) {
            if (!is_string($column)) {
                throw new UiException('bad data');
            }
            if ($column === '----') {
                break;
            }
            if ($grid->getColumn($column) === null) {
                throw new UiException('bad data');
            }
            $displayedColumns[] = $column;
        }

        if (count($displayedColumns) === 0) {
            throw new UiException('you must at least display one column');
        }

        return $displayedColumns;
    }

    protected function prepareUpdateSort(array $params, Grid $grid): array
    {
        if (!array_key_exists('sort', $params)) {
            return [];
        }

        if (
            !is_array($params['sort'])
            || !array_key_exists('column', $params['sort'])
            || !array_key_exists('order', $params['sort'])
            || !is_string($params['sort']['column'])
            || !is_string($params['sort']['order'])
        ) {
            throw new UiException('bad data');
        }

        $column = $params['sort']['column'];
        $order = $params['sort']['order'];

        if (
            $grid->getColumn($column) === null
            || !in_array($order, ['asc', 'desc'], true)
        ) {
            throw new UiException('bad data');
        }

        return [
            'column' => $column,
            'order'  => $order,
        ];
    }

    protected function prepareUpdateFilters(array $params, Grid $grid): array
    {
        if (!array_key_exists('filters', $params)) {
            return [];
        }
        if (!is_array($params['filters'])) {
            throw new UiException('bad data');
        }

        $values = $params['filters'];
        $filters = [];

        foreach ($values as $code => $value) {
            if ($grid->getColumn($code) === null) {
                throw new UiException('bad data');
            }
            $value = (string) $value;
            if ($value !== '') {
                $filters[$code] = $value;
            }
        }

        return $filters;
    }
}
