<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor;

use ArrayIterator;
use Composer\Autoload\ClassLoader;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\Exception;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\PhpAccessor\Annotation\HyperfData;
use PhpAccessor\Runner;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[Listener(priority: 9999)]
class AutoGenerateAccessor implements ListenerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);
        if (empty($config->get('php-accessor.proxy_root_directory'))) {
            return;
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('The process fork failed');
        }

        if ($pid) {
            pcntl_wait($status);
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

    private function genProxyFile(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $this->removeProxies($config->get('php-accessor.proxy_root_directory'));
        $classes = AnnotationCollector::getClassesByAnnotation(HyperfData::class);
        $files = [];
        foreach ($classes as $class => $annotation) {
            $ref = new ReflectionClass($class);
            $files[] = new SplFileInfo($ref->getFileName());
        }
        $finder = new ArrayIterator($files);
        $runner = new Runner(
            finder: $finder,
            dir: $config->get('php-accessor.proxy_root_directory'),
            genMeta: $config->get('php-accessor.gen_meta') == 'yes',
            genProxy: $config->get('php-accessor.gen_proxy') == 'yes',
        );
        $runner->generate();
        $log = $this->container->get(StdoutLoggerInterface::class);
        $logLevel = $config->get('php-accessor.log_level', LogLevel::INFO);
        foreach ($runner->getGeneratedFiles() as $generatedFile) {
            $log->log($logLevel, '[php-accessor]: ' . $generatedFile);
        }

        exit;
    }

    private function removeProxies($dir): void
    {
        $filesystem = new Filesystem();
        if (! $filesystem->exists($dir)) {
            return;
        }

        $finder = (new Finder())->files()->in($dir);
        $filesystem->remove($finder);
    }
}
