<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Spipu\UiBundle\Form\Options\YesNo;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(YesNo::class)]
class YesNoTest extends AbstractTestCase
{
    public function getOption(): OptionsInterface
    {
        $options = new YesNo();

        return $options;
    }

    public function getValues(): array
    {
        return [
            1 => 'spipu.ui.options.value_yes',
            0 => 'spipu.ui.options.value_no',
        ];
    }
}
