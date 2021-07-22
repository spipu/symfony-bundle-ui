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

namespace Spipu\UiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SpipuUiExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     * @param ContainerBuilder $container
     * @return void
     * @SuppressWarnings(PMD.StaticAccess)
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', ['form_themes' => ['@SpipuUi/form_layout.html.twig']]);
    }
}
