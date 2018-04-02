<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Filesystem\Filesystem;

final class Application extends BaseApplication
{
    const APP_NAME = '';
    const APP_VERSION = '';

    private $projectDir = null;

    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);

        $inputDefinition = $this->getDefinition();
        $inputDefinition->addOption(new InputOption('debug', null, InputOption::VALUE_NONE, 'Switches on debug mode.'));
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer($input->hasParameterOption('--debug'));

        if ($container->has('console.command_loader')) {
            $this->setCommandLoader($container->get('console.command_loader'));
        }

        parent::doRun($input, $output);
    }

    /**
     * Create container or fetch from cache if exists.
     *
     * @return ContainerInterface
     */
    private function getContainer(bool $isDebug): ContainerInterface
    {
        $cachePath = $this->getProjectDir().'/cache/ProjectContainer.php';

        $cache = new ConfigCache($cachePath, $isDebug);
        if (!$cache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->getParameterBag()->add($this->getParameters());
            $containerBuilder->addCompilerPass(new AddConsoleCommandPass());

            $loader = new PhpFileLoader($containerBuilder, new FileLocator($this->getProjectDir().'/config'));
            $loader->load('services.php');

            $containerBuilder->compile();

            $this->dumpContainer($cache, $containerBuilder, $isDebug, 'ProjectContainer');
        }

        return require_once $cachePath;
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache
     * @param ContainerBuilder
     * @param bool
     * @param string
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, bool $isDebug, string $class)
    {
        // cache the container
        $dumper = new PhpDumper($container);

        $content = $dumper->dump([
            'class' => $class,
            'file' => $cache->getPath(),
            'as_files' => true,
            'debug' => $isDebug,
        ]);

        $rootCode = array_pop($content);
        $dir = dirname($cache->getPath()).'/';
        $fs = new Filesystem();

        foreach ($content as $file => $code) {
            $fs->dumpFile($dir.$file, $code);
            @chmod($dir.$file, 0666 & ~umask());
        }
        @unlink(dirname($dir.$file).'.legacy');

        $cache->write($rootCode, $container->getResources());
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    private function getParameters(): array
    {
        return [
            'app.project_dir' => $this->getProjectDir(),
        ];
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string
     */
    protected function getProjectDir(): string
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);
            $dir = $rootDir = dirname($r->getFileName());
            while (!file_exists($dir.'/composer.json')) {
                if ($dir === dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }
}
