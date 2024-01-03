<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

#[Command]
class AccessorConfigCommand extends HyperfCommand
{
    public function __construct(
        private ContainerInterface $container,
    ) {
        parent::__construct('hyperf-php-accessor:config');
    }

    public function handle(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $proxyRootDirectory = BASE_PATH . '/' . $config->get('php-accessor.proxy_root_directory', '.php-accessor');
        $this->line(
            '[framework-accessor-config]' . json_encode(
                [
                    'proxy_root_directory' => $proxyRootDirectory,
                ],
                JSON_UNESCAPED_SLASHES
            )
        );
    }
}
