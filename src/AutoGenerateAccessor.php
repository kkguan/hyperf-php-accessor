<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor;

use ArrayIterator;
use Composer\Autoload\ClassLoader;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Exception\Exception;
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
        'scan_directories' => [
            'app',
        ],
        'proxy_root_directory' => '.php-accessor',
        'gen_meta' => 'yes',
        'gen_proxy' => 'yes',
        'log_level' => LogLevel::INFO,
        'scan_cacheable' => false,
        'max_concurrent_processes' => 2,
        'max_files_per_process' => 200,
    ];

    private string $proxyRootDir;

    private string $proxyDir;

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
        $this->proxyDir = $this->proxyRootDir . '/proxy';

        $this->genProxyFile();

        if (! is_dir($this->proxyDir)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*.php')->in($this->proxyDir);
        $classLoader = new ClassLoader();
        $classMap = [];
        foreach ($finder->getIterator() as $value) {
            $classname = str_replace('@', '\\', $value->getBasename('.' . $value->getExtension()));
            $classname = substr($classname, 1);
            $classMap[$classname] = $value->getRealPath();
        }
        $classLoader->addClassMap($classMap);
        $classLoader->register(true);
    }

    private function genProxyFile(): void
    {
        // 如果开启了缓存，且代理文件已经存在，则不再生成
        if ($this->config['scan_cacheable'] === true && is_dir($this->proxyDir)) {
            $finder = new Finder();
            $finder->files()->name('*.php')->in($this->proxyDir);
            if ($finder->count() > 0) {
                return;
            }
        }
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
            genMeta: $this->config['gen_meta'] == 'yes',
            genProxy: $this->config['gen_proxy'] == 'yes',
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
