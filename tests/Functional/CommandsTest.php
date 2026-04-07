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

namespace Spipu\UiBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UiBundle\Command\ResetGridConfigCommand;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ResetGridConfigCommand::class)]
class CommandsTest extends WebTestCase
{
    public function testResetGrid(): void
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
