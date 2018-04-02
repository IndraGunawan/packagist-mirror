<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DumpMetadataCommand extends Command
{
    protected static $defaultName = 'app:metadata:dump';

    protected function configure()
    {
        $this->setDescription('Dump metadata filess.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('You have add a new account.');
    }
}
