<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\IO;

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
