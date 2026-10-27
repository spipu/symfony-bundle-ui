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

namespace Spipu\UiBundle\Service\Ui\PersonalData;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Exception\GridException;
use Spipu\UiBundle\Exception\UiException;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ResetInterface;

class PersonalDataProtection implements ResetInterface
{
    private PropertyAccessorInterface $propertyAccessor;
    private EntityManagerInterface $entityManager;

    /**
     * @var iterable<PersonalDataRuleInterface>
     */
    private iterable $ruleSources;

    /**
     * @var PersonalDataRuleInterface[]|null
     */
    private ?array $rules = null;

    /**
     * Active rule by entity class, null if there is no active rule
     * @var array<string, PersonalDataRuleInterface|null>
     */
    private array $cacheActiveRules = [];

    /**
     * Hidden state by "<entity class>|<entity id>"
     * @var array<string, bool>
     */
    private array $cacheHiddenEntities = [];

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        iterable $rules
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
        $this->ruleSources = $rules;
    }

    public function reset(): void
    {
        $this->cacheActiveRules = [];
        $this->cacheHiddenEntities = [];
    }

    public function isEntityHidden(EntityInterface $entity): bool
    {
        if ($entity->getId() === null) {
            return false;
        }

        $cacheKey = $entity::class . '|' . $entity->getId();
        if (!array_key_exists($cacheKey, $this->cacheHiddenEntities)) {
            $rule = $this->getRuleForClass($entity::class);
            $this->cacheHiddenEntities[$cacheKey] = (($rule !== null) && !$rule->isVisible($entity));
        }

        return $this->cacheHiddenEntities[$cacheKey];
    }

    /**
     * Whether the last field of a path is hidden. Each segment is checked against the rule of the object owning it.
     */
    public function isHidden(EntityInterface $entity, string $path): bool
    {
        $segments = explode('.', $path);
        $lastIndex = count($segments) - 1;
        $current = $entity;

        foreach ($segments as $index => $segment) {
            if ($current instanceof EntityInterface && $this->isHiddenOwnField($current, $segment)) {
                return true;
            }

            if ($index === $lastIndex) {
                return false;
            }

            try {
                $current = $this->propertyAccessor->getValue($current, $segment);
            } catch (ExceptionInterface $exception) {
                throw $this->buildReadException($entity, $path, $exception);
            }

            if ($current === null) {
                return false;
            }

            if (!is_object($current)) {
                throw $this->buildReadException($entity, $path);
            }
        }

        return false;
    }

    /**
     * DQL conditions matching the rows whose personal data are visible, for a filter or a search on a path.
     * One condition per alias owning a protected segment of the path ("main" for the main entity, else the join alias).
     * The aliases already handled by the query are excluded.
     * Format of the generator: function(): string (a new unique parameter name, with the ":" prefix)
     * @param string|null $entityClass
     * @param string $path
     * @param string[] $excludedAliases
     * @param callable $parameterNameGenerator
     * @return array<string, array{0: string, 1: array<string, mixed>}> alias => [DQL condition, parameters]
     */
    public function getVisibleDqlConditions(
        ?string $entityClass,
        string $path,
        array $excludedAliases,
        callable $parameterNameGenerator
    ): array {
        $conditions = [];
        foreach ($this->getFieldRules($entityClass, $path) as [$rule, $alias]) {
            if (in_array($alias, $excludedAliases, true)) {
                continue;
            }
            $conditions[$alias] = $this->getVisibleDqlCondition($rule, $alias, $parameterNameGenerator);
        }

        return $conditions;
    }

    /**
     * Active rule of an entity class, null if there is no rule or if it is not active in the current context.
     */
    private function getRuleForClass(string $entityClass): ?PersonalDataRuleInterface
    {
        if (!array_key_exists($entityClass, $this->cacheActiveRules)) {
            $rule = $this->findRuleForClass($entityClass);
            $this->cacheActiveRules[$entityClass] = null;
            if (($rule !== null) && $rule->isActive()) {
                $this->cacheActiveRules[$entityClass] = $rule;
            }
        }

        return $this->cacheActiveRules[$entityClass];
    }

    private function findRuleForClass(string $entityClass): ?PersonalDataRuleInterface
    {
        $rules = $this->getRules();
        if (array_key_exists($entityClass, $rules)) {
            return $rules[$entityClass];
        }

        foreach ($rules as $ruleClass => $rule) {
            if (is_a($entityClass, $ruleClass, true)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * @return PersonalDataRuleInterface[]
     */
    private function getRules(): array
    {
        if ($this->rules === null) {
            $this->rules = [];
            foreach ($this->ruleSources as $rule) {
                $entityClass = $rule->getEntityClass();
                if (array_key_exists($entityClass, $this->rules)) {
                    throw new UiException('Personal data rule already defined for entity ' . $entityClass);
                }
                $this->rules[$entityClass] = $rule;
            }
        }

        return $this->rules;
    }

    private function isHiddenOwnField(EntityInterface $entity, string $field): bool
    {
        $rule = $this->getRuleForClass($entity::class);

        return ($this->isProtectedOwnField($rule, $field) && $this->isEntityHidden($entity));
    }

    private function isProtectedOwnField(?PersonalDataRuleInterface $rule, string $field): bool
    {
        return ($rule !== null) && in_array($field, $rule->getFields(), true);
    }

    private function buildReadException(
        EntityInterface $entity,
        string $path,
        ?ExceptionInterface $previous = null
    ): GridException {
        return new GridException('Unable to find field ' . $path . ' on object ' . $entity::class, 0, $previous);
    }

    /**
     * Rules protecting the segments of a path, with the alias owning each protected segment.
     * Each segment is checked against the rule of the class owning it, resolved with the Doctrine metadata.
     * The alias is "main" for the main entity, else the join alias (the segment before).
     * @return array<array{0: PersonalDataRuleInterface, 1: string}>
     */
    private function getFieldRules(?string $entityClass, string $path): array
    {
        $fieldRules = [];
        $segments = explode('.', $path);
        $lastIndex = count($segments) - 1;
        $alias = 'main';

        foreach ($segments as $index => $segment) {
            if ($entityClass === null) {
                break;
            }

            $rule = $this->getRuleForClass($entityClass);
            if ($this->isProtectedOwnField($rule, $segment)) {
                $fieldRules[] = [$rule, $alias];
            }

            if ($index < $lastIndex) {
                $entityClass = $this->getAssociationTargetClass($entityClass, $segment);
                $alias = $segment;
            }
        }

        return $fieldRules;
    }

    private function getAssociationTargetClass(string $entityClass, string $association): ?string
    {
        if ($this->entityManager->getMetadataFactory()->isTransient($entityClass)) {
            return null;
        }

        $metadata = $this->entityManager->getClassMetadata($entityClass);
        if (!$metadata->hasAssociation($association)) {
            return null;
        }

        return $metadata->getAssociationTargetClass($association);
    }

    /**
     * Visible condition of a rule on an alias, with its parameters renamed by the given generator.
     * Format of the generator: function(): string (a new unique parameter name, with the ":" prefix)
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function getVisibleDqlCondition(
        PersonalDataRuleInterface $rule,
        string $alias,
        callable $parameterNameGenerator
    ): array {
        [$condition, $ruleParameters] = $rule->getVisibleDqlCondition($alias);

        $parameters = [];
        foreach ($ruleParameters as $name => $value) {
            $parameterName = $parameterNameGenerator();
            $condition = (string) preg_replace(
                '/:' . preg_quote(ltrim((string) $name, ':'), '/') . '\b/',
                $parameterName,
                $condition
            );
            $parameters[$parameterName] = $value;
        }

        return [$condition, $parameters];
    }
}
