<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\Command;

use Indragunawan\PackagistMirror\Application;
use Indragunawan\PackagistMirror\IO\NullIO;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
abstract class Command extends BaseCommand
{
    private $io;

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        if (null === $this->io) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                $this->io = $application->getIO();
            } else {
                $this->io = new NullIO();
            }
        }

        return $this->io;
    }
}
