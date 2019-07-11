<?php
namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use Spipu\UiBundle\Form\Options\ActiveStatus;
use Spipu\UiBundle\Form\Options\OptionsInterface;

class ActiveStatusTest extends AbstractTest
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
