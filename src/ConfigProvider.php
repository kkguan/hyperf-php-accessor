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
                Command\AccessorConfigCommand::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'class_map' => [
                        \PhpAccessor\Attribute\Data::class => BASE_PATH . '/vendor/free2one/hyperf-php-accessor/src/Annotation/Data.php',
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
