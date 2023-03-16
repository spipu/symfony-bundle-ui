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

use Spipu\UiBundle\Service\Ui\UiManagerInterface;
use Spipu\UiBundle\Service\Menu\Manager as MenuManager;
use Spipu\UiBundle\Entity\Menu\Item as MenuItem;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UiExtension extends AbstractExtension
{
    /**
     * @var MenuManager
     */
    private $menuManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * MainController constructor.
     * @param MenuManager $menuManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        MenuManager $menuManager,
        TranslatorInterface $translator
    ) {
        $this->menuManager = $menuManager;
        $this->translator = $translator;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderManager', [$this, 'renderManager']),
            new TwigFunction('getMenu', [$this, 'getMenu']),
            new TwigFunction('getTranslations', [$this, 'getTranslations']),
        ];
    }

    /**
     * @param UiManagerInterface $manager
     * @return void
     */
    public function renderManager(UiManagerInterface $manager): void
    {
        echo $manager->display();
    }

    /**
     * @param string $currentItem
     * @return MenuItem
     */
    public function getMenu(string $currentItem): MenuItem
    {
        return $this->menuManager->buildMenu($currentItem);
    }

    /**
     * @param array $codes
     * @return array
     */
    public function getTranslations(array $codes): array
    {
        $values = [];

        foreach ($codes as $code) {
            $values[$code] = $this->translator->trans($code);
        }

        return $values;
    }
}
