<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Amp\Success;
use App\IO\IOInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Amp\call;
use function Amp\File\get;
use function Amp\File\isfile;
use function Amp\File\link;
use function Amp\File\symlink;
use function Amp\File\unlink;
use function Amp\Promise\wait;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class SymlinkMetadataCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:metadata:symlink';
    private $buildDir;
    private $publicDir;

    public function __construct(string $buildDir, string $publicDir)
    {
        parent::__construct();
        $this->buildDir = $buildDir;
        $this->publicDir = $publicDir;
    }

    protected function configure()
    {
        $this->setDescription('Symlink metadata files, usually executed after deploying');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $this->getIO()->writeError('The command is already running in another process.');

            return 0;
        }

        wait(call(function (IOInterface $io, string $buildDir, string $publicDir) {
            $dir = null;
            $file = yield isfile($buildDir.'/BUILD_DIR');
            if (true === $file) {
                $dir = yield get($buildDir.'/BUILD_DIR');
            }

            if (null === $dir) {
                $io->writeInfo('Build directory not found');

                return new Success([]);
            }
            $buildDir .= '/'.preg_replace('/[^0-9A-Za-z]*/', '', $dir);

            // symlink packages.json file to public
            yield unlink($publicDir.'/packages.json');
            yield link($buildDir.'/packages.json', $publicDir.'/packages.json');
            $io->writeInfo('Creating symlinks for packages.json');

            yield unlink($publicDir.'/p');
            yield link($buildDir.'/p', $publicDir.'/p');
            $io->writeinfo('creating symlinks for provider directory');
        }, $this->getIO(), $this->buildDir, $this->publicDir));
    }
}
