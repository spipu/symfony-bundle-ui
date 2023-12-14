<?php
namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use Spipu\UiBundle\Form\Options\OptionsInterface;
use Spipu\UiBundle\Form\Options\YesNo;

class YesNoTest extends AbstractTest
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
