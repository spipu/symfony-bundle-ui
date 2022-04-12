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

use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Entity\GridConfig as GridConfigEntity;
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
     * @param Security $security
     * @param GridConfigRepository $gridConfigRepository
     * @param TranslatorInterface $translator
     * @param GridIdentifierInterface $gridIdentifier
     * @param UserIdentifierInterface $userIdentifier
     */
    public function __construct(
        Security $security,
        GridConfigRepository $gridConfigRepository,
        TranslatorInterface $translator,
        GridIdentifierInterface $gridIdentifier,
        UserIdentifierInterface $userIdentifier
    ) {
        $this->security = $security;
        $this->gridConfigRepository = $gridConfigRepository;
        $this->translator = $translator;
        $this->gridIdentifier = $gridIdentifier;
        $this->userIdentifier = $userIdentifier;
    }

    /**
     * @param Grid $grid
     * @return GridConfigEntity[]
     */
    public function getUserConfigs(Grid $grid): array
    {
        $gridIdentifier = $this->getGridIdentifier($grid);
        $userIdentifier = $this->getUserIdentifier();

        return $this->gridConfigRepository->getUserConfigs($gridIdentifier, $userIdentifier);
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

        return $this->gridConfigRepository->getUserConfig($gridIdentifier, $userIdentifier, $gridConfigId);
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
     * @return array
     */
    public function getPersonalizeDefinition(Grid $grid): array
    {
        $definition = [
            'columns' => [],
            'configs' => [],
        ];

        foreach ($grid->getColumns() as $column) {
            $definition['columns'][$column->getCode()] = [
                'code'      => $column->getCode(),
                'name'      => $this->translator->trans($column->getName()),
                'position'  => $column->getPosition(),
                'displayed' => $column->isDisplayed(),
            ];
        }

        $configs = $this->getUserConfigs($grid);
        foreach ($configs as $config) {
            $definition['configs'][$config->getId()] = [
                'id'     => $config->getId(),
                'name'   => $config->getName(),
                'config' => $config->getConfig(),
            ];
        }

        return $definition;
    }
}
