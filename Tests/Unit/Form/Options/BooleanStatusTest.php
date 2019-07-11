<?php
namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use Spipu\UiBundle\Form\Options\BooleanStatus;
use Spipu\UiBundle\Form\Options\OptionsInterface;

class BooleanStatusTest extends AbstractTest
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
