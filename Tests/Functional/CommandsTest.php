<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\UiBundle\Tests\Functional;

use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UiBundle\Command\ResetGridConfigCommand;

class CommandsTest extends WebTestCase
{
    public function testResetGrid()
    {
        $commandTester = self::loadCommand(
            ResetGridConfigCommand::class,
            'spipu:ui:grid-config:reset'
        );

        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Reset UI Grid Config', $output);
        $this->assertStringContainsString('=> OK', $output);
    }
}
