<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\Command;

use Indragunawan\PackagistMirror\Repository\Dumper;
use Indragunawan\PackagistMirror\Repository\Repository;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class DumpMetadataCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:metadata:dump';
    private $dumper;
    private $url;

    public function __construct(Dumper $dumper , string $url )
    {
        parent::__construct();
        $this->dumper = $dumper;
        $this->url = $url;
    }

    protected function configure()
    {
        $this->addArgument('url', InputArgument::OPTIONAL, 'what is the url of the repo you want to mirror ?', $this->url);
        $this->setDescription('Dump metadata files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $this->getIO()->writeInfo(sprintf('Start time       : %s', date('c')));
        if (!$this->lock($this->getName().'::dump')) {
            $this->getIO()->writeError('The command is already running in another process.');

            return 0;
        }

        ini_set('memory_limit', '-1');
        gc_enable();

        $repository = new Repository($url, $this->getIO());
        // dump and symlink packages metadata
        $this->dumper->dump($repository);

        // release lock for dump
        $this->release();

        // lock to remove old files
        if ($this->lock($this->getName().'::removeOldFiles')) {
            $this->dumper->removeOldFiles($repository);

            $this->release();
        }
    }
}
