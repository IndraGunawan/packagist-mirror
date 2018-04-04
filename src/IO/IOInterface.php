<?php

declare(strict_types=1);

namespace App\IO;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
interface IOInterface
{
    const VERBOSITY_QUIET = 16;
    const VERBOSITY_NORMAL = 32;
    const VERBOSITY_VERBOSE = 64;
    const VERBOSITY_VERY_VERBOSE = 128;
    const VERBOSITY_DEBUG = 256;

    /**
     * Writes messages to the output.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    public function write($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void;

    /**
     * Writes info messages to the output.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    public function writeInfo($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void;

    /**
     * Writes comment messages to the output.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    public function writeComment($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void;

    /**
     * Writes question messages to the output.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    public function writeQuestion($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void;

    /**
     * Writes error messages to the output.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    public function writeError($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void;

    /**
     * Show memory usage and timing when profiling.
     *
     * @param float $startTime
     */
    public function enableDebugging(float $startTime): void;
}
