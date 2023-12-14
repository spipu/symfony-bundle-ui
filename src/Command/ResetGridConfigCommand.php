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

namespace Spipu\UiBundle\Command;

use Spipu\UiBundle\Repository\GridConfigRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetGridConfigCommand extends Command
{
    private GridConfigRepository $gridConfigRepository;

    public function __construct(
        GridConfigRepository $gridConfigRepository,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->gridConfigRepository = $gridConfigRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('spipu:ui:grid-config:reset')
            ->setDescription('Reset the Spipu UI Grid Config.')
            ->setHelp('This command allows you to reset the Spipu UI Grid Config')
        ;
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Reset UI Grid Config');

        $this->gridConfigRepository->resetDefaults();

        $output->writeln(' => OK');

        return self::SUCCESS;
    }
}
