<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor;

use ArrayIterator;
use Composer\Autoload\ClassLoader;
use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use PhpAccessor\Runner;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[Listener(priority: 9999)]
class AutoGenerateAccessor implements ListenerInterface
{
    private array $config = [
        'is_dev_mode' => true,
        'scan_directories' => [
            'app',
        ],
        'proxy_root_directory' => '.php-accessor',
        'log_level' => LogLevel::DEBUG,
        'max_concurrent_processes' => 2,
        'max_files_per_process' => 200,
    ];

    private string $proxyRootDir;

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
        $this->config = array_merge($this->config, $config->get('php-accessor', []));
        $this->proxyRootDir = BASE_PATH . '/' . $this->config['proxy_root_directory'];
        $proxyDir = $this->proxyRootDir . '/proxy';

        try {
            if ($this->config['is_dev_mode'] || ! is_dir($proxyDir)) {
                $this->genProxyFile();
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
            if (empty($classMap)) {
                return;
            }
            $classLoader->addClassMap($classMap);
            $classLoader->register(true);
        } catch (Exception $e) {
            $log = $this->container->get(StdoutLoggerInterface::class);
            $log->error('[php-accessor]: Failure to generate proxies.(' . $e->getMessage() . ')');
        }
    }

    private function genProxyFile(): void
    {
        // 清理代理文件
        $this->removeProxies();
        // 生成代理文件
        foreach ($this->config['scan_directories'] as $scanDirectory) {
            $finder = new Finder();
            $finder->files()->name('*.php')->in(BASE_PATH . '/' . $scanDirectory);
            $index = 0;
            foreach ($finder->getIterator() as $value) {
                $files[$index][] = $value;
                if (count($files[$index]) >= $this->config['max_files_per_process']) {
                    ++$index;
                }
            }
            // 使用子进程生成代理文件
            $gen = true;
            while ($gen) {
                $runProcessNum = 0;
                for ($i = 0; $i < $this->config['max_concurrent_processes']; ++$i) {
                    $chunk = array_pop($files);
                    if (empty($chunk)) {
                        $gen = false;
                        break;
                    }

                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        throw new Exception('The process fork failed');
                    }

                    if ($pid == 0) {
                        $this->gen($chunk);
                        exit;
                    }

                    ++$runProcessNum;
                }

                // 等待所有子进程结束
                for ($i = 0; $i < $runProcessNum; ++$i) {
                    pcntl_wait($status);
                }
            }
        }
    }

    private function gen(array $files): void
    {
        $finder = new ArrayIterator($files);
        $runner = new Runner(
            finder: $finder,
            dir: $this->proxyRootDir,
            genMeta: $this->config['is_dev_mode'] == true,
            genProxy: true,
        );
        $runner->generate();
        $log = $this->container->get(StdoutLoggerInterface::class);
        $logLevel = $this->config['log_level'];
        foreach ($runner->getGeneratedFiles() as $generatedFile) {
            $log->log($logLevel, '[php-accessor]: ' . $generatedFile);
        }
    }

    private function removeProxies(): void
    {
        $filesystem = new Filesystem();
        if (! $filesystem->exists($this->proxyRootDir)) {
            return;
        }

        $finder = (new Finder())->files()->in($this->proxyRootDir);
        $filesystem->remove($finder);
    }
}
