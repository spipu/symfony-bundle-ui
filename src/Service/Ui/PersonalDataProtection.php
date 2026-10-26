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

namespace Spipu\UiBundle\Service\Ui;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Grid\Grid;
use Spipu\UiBundle\Entity\PersonalDataProtectedInterface;
use Spipu\UiBundle\Exception\GridException;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PersonalDataProtection
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function isProtectedField(?string $entityName, string $field): bool
    {
        if ($entityName === null) {
            return false;
        }

        if (!is_a($entityName, PersonalDataProtectedInterface::class, true)) {
            return false;
        }

        return in_array($field, $entityName::getPersonalDataFields(), true);
    }

    public function getHiddenAtField(string $entityName): string
    {
        return $entityName::getPersonalDataHiddenAtField();
    }

    public function isHidden(EntityInterface $entity, string $field): bool
    {
        if (!($entity instanceof PersonalDataProtectedInterface)) {
            return false;
        }

        if (!in_array($field, $entity::getPersonalDataFields(), true)) {
            return false;
        }

        return $entity->isPersonalDataHidden();
    }

    public function getValue(EntityInterface $entity, string $field): mixed
    {
        if ($this->isHidden($entity, $field)) {
            return null;
        }

        return $this->readPath($entity, $field);
    }

    private function readPath(EntityInterface $entity, string $field): mixed
    {
        $segments = explode('.', $field);
        $lastIndex = count($segments) - 1;
        $current = $entity;

        foreach ($segments as $index => $segment) {
            try {
                $value = $this->propertyAccessor->getValue($current, $segment);
            } catch (ExceptionInterface $exception) {
                throw $this->buildReadException($entity, $field, $exception);
            }

            if ($index === $lastIndex) {
                return $value;
            }

            if ($value === null) {
                return null;
            }

            if (!is_object($value)) {
                throw $this->buildReadException($entity, $field);
            }

            $current = $value;
        }

        return null;
    }

    private function buildReadException(
        EntityInterface $entity,
        string $field,
        ?ExceptionInterface $previous = null
    ): GridException {
        return new GridException('Unable to find field ' . $field . ' on object ' . $entity::class, 0, $previous);
    }

    public function disableSort(Grid $definition): void
    {
        foreach ($definition->getColumns() as $column) {
            if ($this->isProtectedField($definition->getEntityName(), $column->getEntityField())) {
                $column->useSortable(false);
            }
        }
    }
}
