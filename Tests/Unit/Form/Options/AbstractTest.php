<?php
namespace Spipu\UiBundle\Tests\Unit\Form\Options;

use Spipu\UiBundle\Form\Options\OptionsInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    abstract public function getOption(): OptionsInterface;

    abstract public function getValues(): array;

    public function testValues()
    {
        $option = $this->getOption();
        $values = $this->getValues();

        $this->assertSame($values, $option->getOptions());
        $this->assertSame(['' => ' '] + $values, $option->getOptionsWithEmptyValue());

        $this->assertSame(array_flip($values), $option->getOptionsInverse());
        $this->assertSame([' ' => ''] + array_flip($values), $option->getOptionsWithEmptyValueInverse());

        $this->assertSame(true, $option->hasKey(false));
        $this->assertSame(true, $option->hasKey(0));
        $this->assertSame(true, $option->hasKey('0'));

        $this->assertSame(true, $option->hasKey(true));
        $this->assertSame(true, $option->hasKey(1));
        $this->assertSame(true, $option->hasKey('1'));

        $this->assertSame(false, $option->hasKey(2));
    }
}
