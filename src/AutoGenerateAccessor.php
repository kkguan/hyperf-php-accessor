<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor;

use Composer\Autoload\ClassLoader;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\Exception;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\PhpAccessor\Annotation\HyperfData;
use PhpAccessor\Console\Application;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[Listener]
class AutoGenerateAccessor implements ListenerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('The process fork failed');
        }

        if ($pid) {
            pcntl_wait($status);
            $config = $this->container->get(ConfigInterface::class);
            $proxyDir = $config->get('php-accessor.proxy_root_directory') . DIRECTORY_SEPARATOR . 'proxy';
            if (! is_dir($proxyDir)) {
                return;
            }
            $finder = new Finder();
            $finder->files()->name('*.php')->in($proxyDir);

            $classLoader = new ClassLoader();
            $classMap = [];
            foreach ($finder->getIterator() as $value) {
                $classname = str_replace('@', '\\', $value->getBasename('.' . $value->getExtension()));
                $classname = substr($classname, 1);
                $classMap[$classname] = $value->getRealPath();
            }
            $classLoader->addClassMap($classMap);
            $classLoader->register(true);
        } else {
            $this->genProxyFile();
        }
    }

    private function genProxyFile()
    {
        $config = $this->container->get(ConfigInterface::class);
        $this->removeProxies($config->get('php-accessor.proxy_root_directory'));
        $classes = AnnotationCollector::getClassesByAnnotation(HyperfData::class);
        $path = [];
        foreach ($classes as $class => $annotation) {
            $ref = new ReflectionClass($class);
            $path[] = $ref->getFileName();
        }
        $input = new ArrayInput([
            'command' => 'generate',
            'path' => $path,
            '--dir' => $config->get('php-accessor.proxy_root_directory'),
            '--gen-meta' => $config->get('php-accessor.gen_meta'),
            '--gen-proxy' => $config->get('php-accessor.gen_proxy'),
        ]);
        $app = new Application();
        $app->run($input);
    }

    private function removeProxies($dir)
    {
        $filesystem = new Filesystem();
        if (! $filesystem->exists($dir)) {
            return;
        }

        $finder = (new Finder())->files()->in($dir);
        $filesystem->remove($finder);
    }
}
