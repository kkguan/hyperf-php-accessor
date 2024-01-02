<?php

declare(strict_types=1);

use Psr\Log\LogLevel;

return [
    'scan_directories' => [
        'app',
    ],
    'proxy_root_directory' => '.php-accessor',
    'gen_meta' => env('APP_ENV', 'dev') == 'dev' ? 'yes' : 'no',
    'gen_proxy' => 'yes',
    'log_level' => LogLevel::DEBUG,
    'scan_cacheable' => false,
    'max_concurrent_processes' => 2,  // 最大并行进程数
    'max_files_per_process' => 200,   // 进程内最大处理文件数
];
