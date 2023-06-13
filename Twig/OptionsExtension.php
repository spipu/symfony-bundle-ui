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

namespace Spipu\UiBundle\Twig;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Error\Error as TwigError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OptionsExtension extends AbstractExtension
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('label_from_option', [$this, 'getLabelFromOption']),
            new TwigFilter('label_from_option_name', [$this, 'getLabelFromOptionName']),
        ];
    }

    public function getLabelFromOption(mixed $value, OptionsInterface $options): string
    {
        $values = $options->getOptions();

        if (is_object($value) && $value instanceof EntityInterface) {
            $value = $value->getId();
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        if (array_key_exists($value, $values)) {
            $value = $values[$value];
        }

        return (string) $value;
    }

    public function getLabelFromOptionName(mixed $value, string $optionsClassName): string
    {
        /** @var OptionsInterface $options */
        $options = $this->container->get($optionsClassName);
        if (!($options instanceof OptionsInterface)) {
            throw new TwigError('The options classname must implements OptionsInterface');
        }

        return $this->getLabelFromOption($value, $options);
    }
}
