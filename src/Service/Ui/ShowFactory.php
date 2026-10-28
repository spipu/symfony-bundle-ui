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

use Spipu\UiBundle\Service\PersonalData\PersonalDataProtection;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as Twig;

class ShowFactory
{
    private EventDispatcherInterface $eventDispatcher;
    private Twig $twig;
    private PersonalDataProtection $personalDataProtection;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Twig $twig,
        PersonalDataProtection $personalDataProtection
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
        $this->personalDataProtection = $personalDataProtection;
    }

    public function create(EntityDefinitionInterface $formDefinition): ShowManagerInterface
    {
        return new ShowManager(
            $this->eventDispatcher,
            $this->twig,
            $formDefinition,
            $this->personalDataProtection
        );
    }
}
