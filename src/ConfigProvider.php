<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for php accessor',
                    'source' => __DIR__ . '/../publish/php-accessor.php',
                    'destination' => BASE_PATH . '/config/autoload/php-accessor.php',
                ],
            ],
        ];
    }
}
