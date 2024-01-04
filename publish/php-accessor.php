<?php

declare(strict_types=1);

use Psr\Log\LogLevel;

return [
    'is_dev_mode' => true, // 是否开启开发模式,本地开发时建议开启,否则无法配合ide插件使用
    'scan_directories' => [
        'app',
    ],
    'proxy_root_directory' => '.php-accessor',
    'log_level' => LogLevel::DEBUG,
    'max_concurrent_processes' => 2,  // 最大并行进程数
    'max_files_per_process' => 200,   // 进程内最大处理文件数
];
