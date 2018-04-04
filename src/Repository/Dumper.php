<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use Amp\File\FilesystemException;
use Amp\Promise;
use Amp\Success;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;
use function Amp\call;
use function Amp\File\exists;
use function Amp\File\get;
use function Amp\File\isdir;
use function Amp\File\isfile;
use function Amp\File\link;
use function Amp\File\mkdir;
use function Amp\File\put;
use function Amp\File\readlink;
use function Amp\File\rmdir;
use function Amp\File\symlink;
use function Amp\File\unlink;
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
        $cacheBuildDir = wait(call(function (string $buildDir) {
            $file = yield isfile($buildDir.'/BUILD_DIR');
            if (true === $file) {
                return yield get($buildDir.'/BUILD_DIR');
            }

            return new Success(null);
        }, $this->buildDir));
        $cacheBuildDir = preg_replace('/[^0-9A-Za-z]*/', '', $cacheBuildDir);

        $cacheBuildDir = $cacheBuildDir ?: $currentBuildDir;

        $repository->setOutputDir($this->buildDir.'/'.$currentBuildDir);
        $repository->setCacheDir($this->buildDir.'/'.$cacheBuildDir);

        $repository->getIO()->writeInfo(sprintf('Output Directory : %s', $repository->getOutputDir()));
        $repository->getIO()->writeInfo(sprintf('Cache Directory  : %s', $repository->getCacheDir()));

        $repository->getIO()->writeInfo(sprintf('Dump packages from %s', $repository->getUrl()));

        $request = call(function (Repository $repository) {
            $jsonUrl = $repository->getPackagesJsonUrl();
            $repository->getIO()->writeComment(sprintf('Downloading %s', $jsonUrl), true, OutputInterface::VERBOSITY_VERBOSE);

            $response = yield $repository->getHttpClient()->request($jsonUrl);

            return yield $response->getBody();
        }, $repository);

        $repository->setPackagesData($this->json_decode(wait($request), true));

        $providers = $this->downloadProviderListings($repository, $repository->getPackagesData());
        // foreach ($providers as $name => $provider) {
        //     $repository->getIO()->writeInfo(sprintf('Downloading packages from %s provider', $name));
        //     $this->downloadProviderListings($repository, collect($this->json_decode($provider, true)));
        // }

        wait(call(function (Repository $repository) {
            // prepare main packages.json
            $rootFile = $repository->getPackagesData()->toArray();
            if (isset($rootFile['notify'])) {
                $rootFile['notify'] = $repository->getRootFileDataUrl($rootFile['notify']);
            }
            if (isset($rootFile['notify-batch'])) {
                $rootFile['notify-batch'] = $repository->getRootFileDataUrl($rootFile['notify-batch']);
            }
            if (isset($rootFile['search'])) {
                $rootFile['search'] = $repository->getRootFileDataUrl($rootFile['search']);
            }

            yield put($repository->getOutputFilePath('packages.json'), $this->json_encode($rootFile));

            // symlink packages.json file to public
            $exists = yield isfile($this->publicDir.'/packages.json');
            if (true === $exists) {
                yield unlink($this->publicDir.'/packages.json');
            }
            yield link($repository->getOutputFilePath('packages.json'), $this->publicDir.'/packages.json');
            $repository->getIO()->writeInfo('Creating symlinks for packages.json');

            // symlink provider directory to public
            $exist = yield isdir($this->publicDir.'/p');
            $link = null;
            try {
                $link = yield readlink($this->publicDir.'/p');
            } catch (FilesystemException $e) {
                // do nothing
            }
            if ($link !== $repository->getOutputFilePath('p')) {
                if (true === $exist) {
                    if (null === $link) {
                        yield rmdir($this->publicDir.'/p');
                    } else {
                        yield unlink($this->publicDir.'/p');
                    }
                }
                yield link($repository->getOutputFilePath('p'), $this->publicDir.'/p');
                $repository->getio()->writeinfo('creating symlinks for provider directory');

                yield rmdir($repository->getCacheDir());
                $repository->getio()->writeinfo('removing provider directory '.$repository->getCacheDir());
            }
        }, $repository));

        if ($repository->getOutputDir() !== $repository->getCacheDir()) {
            wait(call(function (string $buildDir, string $buildDirValue) {
                yield put($buildDir.'/BUILD_DIR', $buildDirValue);
            }, $this->buildDir, $currentBuildDir));
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
                if (0 === $i++ % 35) {
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
                if (false === $loadFromCache && $repository->getOutputDir() === $repository->getCacheDir()) {
                    return new Success('{}');
                }

                $repository->getIO()->writeComment(sprintf(' - Reading %s from cache', $filename), true, OutputInterface::VERBOSITY_DEBUG);

                $content = yield get($repository->getCacheFilePath($filename));
                yield put($repository->getOutputFilePath($filename), $content);
                yield unlink($repository->getCacheFilePath($filename));

                return $content;
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
