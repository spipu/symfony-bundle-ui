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

namespace Spipu\UiBundle;

use Spipu\CoreBundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class SpipuUiBundle extends AbstractBundle
{
    /**
     * @param ContainerConfigurator $container
     * @param ContainerBuilder $builder
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig', ['form_themes' => ['@SpipuUi/form_layout.html.twig']]);
    }
}
