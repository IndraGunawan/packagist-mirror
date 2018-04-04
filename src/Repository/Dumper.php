<?php

declare(strict_types=1);

namespace App\Repository;

use Amp\Promise;
use Amp\Success;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;
use function Amp\call;
use function Amp\File\exists;
use function Amp\File\get;
use function Amp\File\isdir;
use function Amp\File\mkdir;
use function Amp\File\put;
use function Amp\Promise\all;
use function Amp\Promise\wait;

/**
 * @author Indra Gunawan <hello@indra.my.id>
 */
final class Dumper
{
    private $buildDir;
    private $publicDir;

    public function __construct(string $buildDir, string $publicDir)
    {
        $this->buildDir = $buildDir;
        $this->publicDir = $publicDir;
    }

    public function dump(Repository $repository)
    {
        $currentBuildDir = date('Ymd');

        $repository->setOutputDir($this->buildDir.'/'.$currentBuildDir);
        $repository->setCacheDir($this->buildDir.'/'.$currentBuildDir);

        $repository->getIO()->writeInfo(sprintf('Dump packages from %s', $repository->getUrl()));

        $request = call(function (Repository $repository) {
            $jsonUrl = $repository->getPackagesJsonUrl();
            $repository->getIO()->writeComment(sprintf('Downloading %s', $jsonUrl), true, OutputInterface::VERBOSITY_VERBOSE);

            $response = yield $repository->getHttpClient()->request($jsonUrl);

            return yield $response->getBody();
        }, $repository);

        $repository->setPackagesData($this->json_decode(wait($request), true));

        $providers = $this->downloadProviderListings($repository, $repository->getPackagesData());
        foreach ($providers as $name => $provider) {
            $repository->getIO()->writeInfo(sprintf('Downloading packages from %s provider', $name));
            $this->downloadProviderListings($repository, collect($this->json_decode($provider, true)));
        }
    }

    private function downloadProviderListings(Repository $repository, Collection $data)
    {
        $promises = [];
        if ($repository->getPackagesData()->has('providers-url') && is_array($data->get('provider-includes'))) {
            foreach ($data->get('provider-includes') as $name => $metadata) {
                $filename = str_replace('%hash%', $metadata['sha256'], $name);

                $promises[$filename] = $this->downloadAndSaveFile($repository, $filename);
            }
        } elseif ($repository->getPackagesData()->has('providers-url') && is_array($data->get('providers'))) {
            $i = 1;
            foreach ($data->get('providers') as $name => $metadata) {
                $filename = str_replace(['%package%', '%hash%'], [$name, $metadata['sha256']], $repository->getPackagesData()->get('providers-url'));

                $promises[$filename] = $this->downloadAndSaveFile($repository, $filename);

                // batch processing
                if (0 === $i++ % 30) {
                    wait(all($promises));
                    $promises = [];
                }
            }
        }

        return wait(all($promises));
    }

    private function downloadAndSaveFile(Repository $repository, string $filename, bool $loadFromCache = false): Promise
    {
        return call(function (Repository $repository, string $filename, bool $loadFromCache) {
            // if file already exists then use the cached file
            $exists = yield exists($repository->getCacheFilePath($filename));
            if ($exists) {
                if (false === $loadFromCache) {
                    return new Success('{}');
                }

                $repository->getIO()->writeComment(sprintf(' - Reading %s from cache', $filename), true, OutputInterface::VERBOSITY_DEBUG);

                return yield get($repository->getCacheFilePath($filename));
            }

            $response = yield $repository->getHttpClient()->request($repository->getBaseUrl().'/'.$filename);
            $repository->getIO()->writeComment(sprintf(' - Downloading %s', $filename), true, OutputInterface::VERBOSITY_DEBUG);

            // create directory if not exists
            $handler = yield isdir($repository->getOutputFileDir($filename));
            if (false === $handler) {
                yield mkdir($repository->getOutputFileDir($filename), 0755, true);
            }

            $content = yield $response->getBody();
            yield put($repository->getOutputFilePath($filename), $content);

            return $content;
        }, $repository, $filename, $loadFromCache);
    }

    private function json_decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: '.json_last_error_msg()
            );
        }

        return $data;
    }

    private function json_encode($value, $options = 0, $depth = 512)
    {
        $json = \json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: '.json_last_error_msg()
            );
        }

        return $json;
    }
}
