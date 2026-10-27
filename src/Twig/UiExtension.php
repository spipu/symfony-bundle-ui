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
use Spipu\UiBundle\Service\Ui\PersonalData\PersonalDataProtection;
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
    private PersonalDataProtection $personalDataProtection;

    public function __construct(
        MenuManager $menuManager,
        TranslatorInterface $translator,
        PersonalDataProtection $personalDataProtection
    ) {
        $this->menuManager = $menuManager;
        $this->translator = $translator;
        $this->personalDataProtection = $personalDataProtection;
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
            new TwigFunction('is_personal_data_hidden_entity', [$this, 'isPersonalDataHiddenEntity']),
            new TwigFunction('is_personal_data_hidden_field', [$this, 'isPersonalDataHiddenField']),
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

    public function isClosure(mixed $variable): bool
    {
        return $variable instanceof \Closure;
    }

    public function executeClosure(callable $closure, array $variables = []): mixed
    {
        return call_user_func_array($closure, $variables);
    }

    public function isPersonalDataHiddenEntity(EntityInterface $entity): bool
    {
        return $this->personalDataProtection->isEntityHidden($entity);
    }

    public function isPersonalDataHiddenField(EntityInterface $entity, string $field): bool
    {
        return $this->personalDataProtection->isHidden($entity, $field);
    }
}
