<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indragunawan\PackagistMirror\Repository;

use Amp\File;
use Amp\File\FilesystemException;
use Amp\Promise;
use Amp\Success;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;
use function Amp\call;

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

    public function dump(Repository $repository): void
    {
        // only create a new build dir once a month
        $currentBuildDir = date('Ym');
        $cacheBuildDir = $this->getCacheDir($this->buildDir);
        $cacheBuildDir = preg_replace('/[^0-9A-Za-z]*/', '', $cacheBuildDir);
        $cacheBuildDir = $cacheBuildDir ?: $currentBuildDir;

        $repository->setOutputDir($this->buildDir.'/'.$currentBuildDir);
        $repository->setCacheDir($this->buildDir.'/'.$cacheBuildDir);

        $repository->getIO()->writeInfo(sprintf('Output directory : %s', $repository->getOutputDir()));
        $repository->getIO()->writeInfo(sprintf('Cache directory  : %s', $repository->getCacheDir()));

        Promise\wait(call(function (Repository $repository) {
            yield $this->createDirectory($repository->getOutputDir());

            $repository->getIO()->writeInfo(sprintf('Downloading providers from %s', $repository->getUrl()));
            $jsonUrl = $repository->getPackagesJsonUrl();
            $repository->getIO()->writeComment(sprintf('Downloading %s', $jsonUrl), true, OutputInterface::VERBOSITY_VERBOSE);

            $response = yield $repository->getHttpClient()->request($jsonUrl);

            $repository->setPackagesData($this->json_decode(yield $response->getBody(), true));

            yield $this->downloadProviderListings($repository, $repository->getPackagesData());

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
            yield File\put($repository->getOutputFilePath('packages.json'), $this->json_encode($rootFile));

            // symlink packages.json file to public
            $repository->getIO()->writeInfo('Creating symlinks for packages.json');
            yield File\unlink($this->publicDir.'/packages.json');
            yield File\link($repository->getOutputFilePath('packages.json'), $this->publicDir.'/packages.json');

            // symlink provider directory to public
            $link = null;
            try {
                $link = yield File\readlink($this->publicDir.'/p');
            } catch (FilesystemException $e) {
                // do nothing
            }
            $exist = yield File\isdir($this->publicDir.'/p');
            if ($link !== $repository->getOutputFilePath('p')) {
                if (true === $exist && null === $link) {
                    yield $this->removeDirectory($this->publicDir.'/p');
                }
                $repository->getIO()->writeinfo('Creating symlinks for provider directory');
                yield File\unlink($this->publicDir.'/p');
                yield File\link($repository->getOutputFilePath('p'), $this->publicDir.'/p');
            }
        }, $repository));

        Promise\wait(call(function (string $buildDir, string $buildDirValue) {
            yield File\put($buildDir.'/BUILD_DIR', $buildDirValue);
        }, $this->buildDir, $currentBuildDir));
    }

    private function downloadProviderListings(Repository $repository, Collection $data): Promise
    {
        return call(function (Repository $repository, Collection $data) {
            $providers = [];
            if ($repository->getPackagesData()->has('providers-url') && is_array($data->get('provider-includes'))) {
                foreach ($data->get('provider-includes') as $name => $metadata) {
                    $filename = str_replace('%hash%', $metadata['sha256'], $name);
                    $providers[$filename] = $this->downloadAndSaveFile($repository, $filename);
                }
                foreach (yield Promise\all($providers) as $filename => $content) {
                    $repository->getIO()->writeInfo(sprintf('Downloading packages from %s provider', $filename));
                    yield $this->downloadProviderListings($repository, collect(json_decode($content, true)));
                }
            } elseif ($repository->getPackagesData()->has('providers-url') && is_array($data->get('providers'))) {
                $i = 0;
                foreach ($data->get('providers') as $name => $metadata) {
                    $filename = str_replace(['%package%', '%hash%'], [$name, $metadata['sha256']], $repository->getPackagesData()->get('providers-url'));

                    $providers[] = $this->downloadAndSaveFile($repository, $filename);

                    // batch process
                    if (0 === ++$i % 200) {
                        yield Promise\all($providers);
                        $providers = [];
                    }
                }
            } elseif (is_array($data->get('includes'))) {
                foreach ($data->get('includes') as $include => $metadata) {
                    $providers[] = $this->downloadAndSaveFile($repository, $include);
                }
                yield Promise\all($providers);
                $providers = [];
            }

            yield Promise\all($providers);
        }, $repository, $data);
    }

    private function downloadAndSaveFile(Repository $repository, string $filename, bool $loadFromCache = false): Promise
    {
        return call(function (Repository $repository, string $filename, bool $loadFromCache) {
            // if file already exists then use the cached file
            $exists = yield File\exists($repository->getCacheFilePath($filename));
            if ($exists) {
                if (false === $loadFromCache && $repository->getOutputDir() === $repository->getCacheDir()) {
                    return yield new Success('{}');
                }

                $repository->getIO()->writeComment(sprintf(' - Reading %s from cache', $filename), true, OutputInterface::VERBOSITY_DEBUG);

                $content = yield File\get($repository->getCacheFilePath($filename));
                yield $this->createDirectory($repository->getOutputFileDir($filename));
                yield File\put($repository->getOutputFilePath($filename), $content);

                return $content;
            } else {
                $repository->getIO()->writeComment(sprintf(' - Downloading %s', $filename), true, OutputInterface::VERBOSITY_DEBUG);
                $response = yield $repository->getHttpClient()->request($repository->getBaseUrl().'/'.$filename);

                $content = yield $response->getBody();
                yield $this->createDirectory($repository->getOutputFileDir($filename));
                yield File\put($repository->getOutputFilePath($filename), $content);

                return $content;
            }
        }, $repository, $filename, $loadFromCache);
    }

    private function createDirectory(string $path): Promise
    {
        return call(function (string $path) {
            // create directory if not exists
            $isDir = yield File\isdir($path);
            if (false === $isDir) {
                yield File\mkdir($path, 0755, true);
            }
        }, $path);
    }

    private function removeDirectory(string $directory): Promise
    {
        $promise = call(function (string $directory) {
            $isDir = yield File\isdir($directory);
            if (true === $isDir) {
                foreach (yield File\scandir($directory) as $dir) {
                    yield $this->removeDirectory($directory.'/'.$dir);
                }
                yield File\rmdir($directory);
            } else {
                yield File\unlink($directory);
            }
        }, rtrim($directory, '/'));

        clearstatcache();

        return $promise;
    }

    private function json_decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: '.json_last_error_msg()
            );
        }

        return $data;
    }

    private function json_encode($value, int $options = 0, int $depth = 512)
    {
        $json = \json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: '.json_last_error_msg()
            );
        }

        return $json;
    }

    private function getCacheDir(string $buildDir): string
    {
        return Promise\wait(call(function (string $buildDir) {
            $isDir = yield File\isdir($buildDir);
            if (false === $isDir) {
                yield File\mkdir($buildDir);
            }
            $file = yield File\isfile($buildDir.'/BUILD_DIR');
            if (true === $file) {
                return yield File\get($buildDir.'/BUILD_DIR');
            }

            return yield new Success('');
        }, $buildDir));
    }

    public function isNeedToRemoveOldFiles(Repository $repository): bool
    {
        // need to cache old file when output dir is not same with cache dir
        return $repository->getCacheDir() !== $repository->getOutputDir();
    }

    public function removeOldFiles(Repository $repository): void
    {
        Promise\wait(call(function (Repository $repository) {
            if ($repository->getCacheDir() !== $repository->getOutputDir()) {
                $repository->getIO()->writeInfo('Removing old files');
                $repository->getIO()->writeComment(sprintf(' - Removing provider directory %s', $repository->getCacheDir()), true, OutputInterface::VERBOSITY_DEBUG);
                yield $this->removeDirectory($repository->getCacheDir());
            } elseif ('00' === date('i')) {
                // remove outdated files every hour
                $repository->getIO()->writeInfo('Removing old files');
                $timeToRemove = strtotime('30 minutes ago');
                $packages = yield $this->loadPackageListings($repository, $repository->getPackagesData()->toArray());
                $grouped = yield new Success($packages->groupBy(function ($item) {
                    return substr($item, 0, strrpos($item, '/'));
                }));

                foreach ($grouped as $path => $filenames) {
                    $filesToRemove = [];
                    $filenames = $filenames->mapWithKeys(function ($item) {
                        $item = substr($item, strrpos($item, '/') + 1);

                        return [explode('$', $item)[0] => $item];
                    });
                    $dir = $repository->getOutputFilePath($path);

                    $isDir = yield File\isdir($dir);
                    if (true === $isDir) {
                        foreach (yield File\scandir($dir) as $file) {
                            // check the filename without hash
                            // if the file with hash is not in current list then may be we can delete the file
                            if ($filenames->has(explode('$', $file)[0]) && !in_array($file, $filenames->all(), true)) {
                                $isFile = yield File\isfile($dir.'/'.$file);
                                if (true === $isFile) {
                                    $ctime = yield File\ctime($dir.'/'.$file);
                                    if ($ctime < $timeToRemove) {
                                        $repository->getIO()->writeComment(sprintf(' - Removing provider %s', $dir.'/'.$file), true, OutputInterface::VERBOSITY_DEBUG);
                                        $filesToRemove[] = File\unlink($dir.'/'.$file);
                                    }
                                }
                            }
                        }
                    }

                    yield Promise\all($filesToRemove);
                }
                clearstatcache();
            }
        }, $repository));
    }

    /**
     * Load all providers, used for deleting old message.
     *
     * @param Repository $repository
     * @param array      $data
     *
     * @return Collection
     */
    public function loadPackageListings(Repository $repository, array $data): Promise
    {
        return call(function (Repository $repository, array $data) {
            if (isset($data['providers'])) {
                return yield new Success(collect($data['providers'])->map(function ($metadata, $name) use ($repository) {
                    return str_replace(['%package%', '%hash%'], [$name, $metadata['sha256']], $repository->getPackagesData()->get('providers-url'));
                })->values());
            }

            $packages = new Collection();
            if ($repository->getPackagesData()->has('providers-url') && isset($data['provider-includes'])) {
                foreach ($data['provider-includes'] as $include => $metadata) {
                    $filename = str_replace('%hash%', $metadata['sha256'], $include);
                    $packages->push($filename);
                    $providers = yield File\get($repository->getOutputFilePath($filename));
                    $packages = $packages->concat(yield $this->loadPackageListings($repository, json_decode($providers, true)));
                }
            }

            return yield new Success($packages);
        }, $repository, $data);
    }
}
