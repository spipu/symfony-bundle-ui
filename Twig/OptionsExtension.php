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

namespace Spipu\UiBundle\Twig;

use Spipu\UiBundle\Form\Options\OptionsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OptionsExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * OptionsExtension constructor.
     * @param ContainerInterface $container
     */
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

    /**
     * @param mixed $value
     * @param OptionsInterface $options
     * @return mixed
     */
    public function getLabelFromOption($value, OptionsInterface $options)
    {
        $values = $options->getOptions();

        if (is_bool($value)) {
            $value = (int) $value;
        }

        if (array_key_exists($value, $values)) {
            $value = $values[$value];
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param string $optionsClassName
     * @return mixed
     * @throws \Twig_Error
     */
    public function getLabelFromOptionName($value, string $optionsClassName)
    {
        /** @var OptionsInterface $options */
        $options = $this->container->get($optionsClassName);
        if (!($options instanceof OptionsInterface)) {
            throw new \Twig_Error('The options classname must implements OptionsInterface');
        }

        return $this->getLabelFromOption($value, $options);
    }
}
