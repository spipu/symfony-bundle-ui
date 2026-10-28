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

namespace Spipu\UiBundle\Service\PersonalData;

use Spipu\UiBundle\Entity\EntityInterface;

/**
 * Personal data protection rule of an entity.
 * The service must have the spipu.ui.personal-data.rule tag.
 */
interface PersonalDataRuleInterface
{
    /**
     * @return class-string<EntityInterface>
     */
    public function getEntityClass(): string;

    /**
     * Whether the rule applies in the current context (for example: false for an admin with a full view).
     * An inactive rule is ignored: nothing hidden, no search condition, edition allowed.
     */
    public function isActive(): bool;

    /**
     * Protected fields: own fields of the entity only (no dotted path)
     * @return string[]
     */
    public function getFields(): array;

    /**
     * Whether the personal data of this entity are visible.
     * Must match exactly the rows selected by getVisibleDqlCondition(), NULL values included.
     */
    public function isVisible(EntityInterface $entity): bool;

    /**
     * DQL condition matching the rows whose personal data are visible, and its parameters.
     * The parameter names are free: they are renamed by the data provider to avoid collisions.
     * @return array{0: string, 1: array<string, mixed>}
     */
    public function getVisibleDqlCondition(string $alias): array;
}
