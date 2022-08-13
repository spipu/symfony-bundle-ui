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
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class GridConfig
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var GridConfigRepository
     */
    private $gridConfigRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var GridIdentifierInterface
     */
    private $gridIdentifier;

    /**
     * @var UserIdentifierInterface
     */
    private $userIdentifier;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param Security $security
     * @param GridConfigRepository $gridConfigRepository
     * @param TranslatorInterface $translator
     * @param GridIdentifierInterface $gridIdentifier
     * @param UserIdentifierInterface $userIdentifier
     * @param EntityManagerInterface $entityManager
     */
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

        if (empty($configs)) {
            $configs = [$this->getDefaultUserConfig($grid)];
        }

        return $configs;
    }

    /**
     * @param Grid $grid
     * @param int $gridConfigId
     * @return GridConfigEntity|null
     */
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

    /**
     * @param Grid $grid
     * @return GridConfigEntity
     */
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

    /**
     * @param Grid $grid
     * @param string $name
     * @return GridConfigEntity
     */
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

    /**
     * @param Grid $grid
     * @return string
     */
    private function getGridIdentifier(Grid $grid): string
    {
        return $this->gridIdentifier->getIdentifier($grid);
    }

    /**
     * @return string
     */
    private function getUserIdentifier(): string
    {
        $user = $this->security->getUser();
        return $this->userIdentifier->getIdentifier($user);
    }

    /**
     * @param Grid $grid
     * @param int|null $currentConfigId
     * @return array
     */
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

    /**
     * @param Grid $grid
     * @param string $action
     * @param array $params
     * @return GridConfigEntity|null
     * @throws UiException
     */
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

    /**
     * @param Grid $grid
     * @param array $params
     * @return GridConfigEntity|null
     */
    private function makeActionCreate(Grid $grid, array $params): ?GridConfigEntity
    {
        if (!array_key_exists('name', $params) || !is_string($params['name'])) {
            return null;
        }

        $name = trim(strip_tags($params['name']));
        if ($name === '') {
            return null;
        }

        return $this->createUserConfig($grid, $name);
    }

    /**
     * @param Grid $grid
     * @param array $params
     * @return GridConfigEntity|null
     */
    private function makeActionSelect(Grid $grid, array $params): ?GridConfigEntity
    {
        if (!array_key_exists('id', $params) || !is_numeric($params['id'])) {
            return null;
        }

        return $this->getUserConfig($grid, (int) $params['id']);
    }

    /**
     * @param Grid $grid
     * @param array $params
     * @return GridConfigEntity|null
     */
    private function makeActionDelete(Grid $grid, array $params): ?GridConfigEntity
    {
        $gridConfig = $this->makeActionSelect($grid, $params);
        if ($gridConfig && !$gridConfig->isDefault()) {
            $this->entityManager->remove($gridConfig);
            $this->entityManager->flush();
        }

        return $this->getDefaultUserConfig($grid);
    }

    /**
     * @param Grid $grid
     * @param array $params
     * @return GridConfigEntity|null
     * @throws UiException
     */
    private function makeActionUpdate(Grid $grid, array $params): ?GridConfigEntity
    {
        $gridConfig = $this->makeActionSelect($grid, $params);
        if (
            !$gridConfig
            || !array_key_exists('columns', $params)
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

        $gridConfig->setConfig(
            [
                'columns' => $displayedColumns,
            ]
        );

        $this->entityManager->flush();


        return $gridConfig;
    }
}
