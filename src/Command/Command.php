<?php

declare(strict_types=1);

namespace App\Command;

use App\Application;
use App\IO\NullIO;
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
