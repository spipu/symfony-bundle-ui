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
    private MenuManager $menuManager;
    private TranslatorInterface $translator;

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
            new TwigFunction('isClosure', [$this, 'isClosure']),
            new TwigFunction('executeClosure', [$this, 'executeClosure']),
        ];
    }

    public function renderManager(UiManagerInterface $manager): void
    {
        echo $manager->display();
    }

    public function getMenu(string $currentItem): MenuItem
    {
        return $this->menuManager->buildMenu($currentItem);
    }

    public function getTranslations(array $codes): array
    {
        $values = [];

        foreach ($codes as $code) {
            $values[$code] = $this->translator->trans($code);
        }

        return $values;
    }

    /**
     * @param mixed $variable
     * @return bool
     */
    public function isClosure($variable): bool
    {
        return $variable instanceof \Closure;
    }

    /**
     * @param callable $closure
     * @param array $variables
     * @return mixed
     */
    public function executeClosure(callable $closure, array $variables = [])
    {
        return call_user_func_array($closure, $variables);
    }
}
