<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\Repository;

use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Indragunawan\PackagistMirror\IO\IOInterface;
use Tightenco\Collect\Support\Collection;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class Repository
{
    private $url;
    private $io;
    private $baseUrl;
    private $httpClient;

    private $cacheDir;
    private $outputDir;

    private $packagesData;

    public function __construct(string $url, IOInterface $io)
    {
        if (!preg_match('{^[\w.]+\??://}', $url)) {
            // assume http as the default protocol
            $url = 'http://'.$url;
        }
        $url = rtrim($url, '/');

        if ('https?' === substr($url, 0, 6)) {
            $url = (extension_loaded('openssl') ? 'https' : 'http').substr($url, 6);
        }

        $this->url = $url;
        $this->baseUrl = rtrim(preg_replace('{(?:/[^/\\\\]+\.json)?(?:[?#].*)?$}', '', $url), '/');
        $this->io = $io;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIO(): IOInterface
    {
        return $this->io;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getHttpClient(): Client
    {
        if (null === $this->httpClient) {
            $this->httpClient = new DefaultClient();
            $this->httpClient->setOption(Client::OP_DEFAULT_HEADERS, [
                'Encoding' => 'gzip',
                'User-Agent' => 'https://github.com/Indragunawan/packagist-mirror',
            ]);
            $this->httpClient->setOption(Client::OP_TRANSFER_TIMEOUT, 0);
        }

        return $this->httpClient;
    }

    public function setCacheDir(string $cacheDir): self
    {
        $this->cacheDir = rtrim($cacheDir, '/');

        return $this;
    }

    public function getCacheDir(): ?string
    {
        return $this->cacheDir;
    }

    public function getCacheFilePath(string $filename): string
    {
        return $this->getCacheDir().'/'.ltrim($filename, '/');
    }

    public function getCacheFileDir(string $filename): string
    {
        return dirname($this->getCacheFilePath($filename));
    }

    public function setOutputDir(string $outputDir): self
    {
        $this->outputDir = rtrim($outputDir, '/');

        return $this;
    }

    public function getOutputDir(): ?string
    {
        return $this->outputDir;
    }

    public function getOutputFilePath(string $filename): string
    {
        return $this->getOutputDir().'/'.ltrim($filename, '/');
    }

    public function getOutputFileDir(string $filename): string
    {
        return dirname($this->getOutputFilePath($filename));
    }

    public function getPackagesJsonUrl(): string
    {
        $jsonUrlParts = parse_url($this->url);
        if (isset($jsonUrlParts['path']) && false !== strpos($jsonUrlParts['path'], '.json')) {
            return $this->url;
        }

        return $this->baseUrl.'/packages.json';
    }

    public function getRootFileDataUrl($data): string
    {
        if (0 === strpos($data, '/')) {
            return $this->baseUrl.$data;
        }

        return $data;
    }

    /**
     * @param mixed $packagesData
     *
     * @return self
     */
    public function setPackagesData($packagesData): self
    {
        $this->packagesData = new Collection($packagesData);

        return $this;
    }

    public function getPackagesData(): ?Collection
    {
        return $this->packagesData;
    }
}
