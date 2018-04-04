<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use App\Repository\Dumper;
use App\Repository\Repository;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class DumpMetadataCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:metadata:dump';
    private $dumper;

    public function __construct(Dumper $dumper)
    {
        parent::__construct();
        $this->dumper = $dumper;
    }

    protected function configure()
    {
        $this->setDescription('Dump metadata files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $this->getIO()->writeError('The command is already running in another process.');

            return 0;
        }

        gc_enable();

        $repository = new Repository('https://packagist.org', $this->getIO());
        $this->dumper->dump($repository);
    }
}
