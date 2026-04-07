<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\UiBundle\Form\Options\ActiveStatus;
use Spipu\UiBundle\Form\Options\OptionsInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ActiveStatus::class)]
class ActiveStatusTest extends AbstractTestCase
{
    public function getOption(): OptionsInterface
    {
        $options = new ActiveStatus();

        return $options;
    }

    public function getValues(): array
    {
        return [
            1 => 'spipu.ui.options.value_enabled',
            0 => 'spipu.ui.options.value_disabled',
        ];
    }
}
