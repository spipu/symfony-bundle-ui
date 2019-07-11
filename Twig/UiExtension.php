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

use Spipu\UiBundle\Service\Ui\UiManagerInterface;
use Spipu\UiBundle\Service\Menu\Manager as MenuManager;
use Spipu\UiBundle\Entity\Menu\Item as MenuItem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UiExtension extends AbstractExtension
{
    /**
     * @var MenuManager
     */
    private $menuManager;

    /**
     * MainController constructor.
     * @param MenuManager $menuManager
     */
    public function __construct(MenuManager $menuManager)
    {
        $this->menuManager = $menuManager;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderManager', [$this, 'renderManager']),
            new TwigFunction('getMenu', [$this, 'getMenu']),
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
}
