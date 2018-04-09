<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\IO;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class ConsoleIO implements IOInterface
{
    private $output;
    private $startTime;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $this->doWrite($messages, $newLine, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function writeInfo($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $messages = array_map(function ($message) {
            return sprintf('<info>%s</>', $message);
        }, (array) $messages);

        $this->doWrite($messages, $newLine, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function writeComment($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $messages = array_map(function ($message) {
            return sprintf('<comment>%s</>', $message);
        }, (array) $messages);

        $this->doWrite($messages, $newLine, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function writeQuestion($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $messages = array_map(function ($message) {
            return sprintf('<question>%s</>', $message);
        }, (array) $messages);

        $this->doWrite($messages, $newLine, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($messages, bool $newLine = true, int $verbosity = self::VERBOSITY_NORMAL): void
    {
        $messages = array_map(function ($message) {
            return sprintf('<error>%s</>', $message);
        }, (array) $messages);

        $this->doWrite($messages, $newLine, $verbosity);
    }

    /**
     * {@inheritdoc}
     */
    public function enableDebugging(float $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * Execute the writer.
     *
     * @param string|array $messages
     * @param bool         $newLine
     * @param int          $verbosity
     */
    private function doWrite($messages, bool $newLine, int $verbosity): void
    {
        if (null !== $this->startTime) {
            $memoryUsage = memory_get_usage() / 1024 / 1024;
            $timeSpent = microtime(true) - $this->startTime;
            $messages = array_map(function ($message) use ($memoryUsage, $timeSpent) {
                return sprintf('[%.1fMB/%.2fs] %s', $memoryUsage, $timeSpent, $message);
            }, (array) $messages);
        }

        $this->output->write($messages, $newLine, $verbosity);
    }
}
