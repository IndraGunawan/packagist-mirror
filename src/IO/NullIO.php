<?php

declare(strict_types=1);

namespace App\IO;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class NullIO implements IOInterface
{
    /**
     * {@inheritdoc}
     */
    public function write($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function writeInfo($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function writeComment($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function writeQuestion($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($message, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function enableDebugging(float $startTime): void
    {
        // do nothing
    }
}
