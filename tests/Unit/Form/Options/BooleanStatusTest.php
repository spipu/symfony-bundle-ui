<?php

declare(strict_types=1);

namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\UiBundle\Form\Options\BooleanStatus;
use Spipu\UiBundle\Form\Options\OptionsInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(BooleanStatus::class)]
class BooleanStatusTest extends AbstractTestCase
{
    public function getOption(): OptionsInterface
    {
        $options = new BooleanStatus();

        return $options;
    }

    public function getValues(): array
    {
        return [
            1 => 'spipu.ui.options.value_true',
            0 => 'spipu.ui.options.value_false',
        ];
    }
}
