<?php

declare(strict_types=1);

$appEnv = env('APP_ENV', 'dev');
$genMeta = $appEnv == 'dev' ? 'yes' : 'no';

return [
    'proxy_root_directory' => BASE_PATH . DIRECTORY_SEPARATOR . '.php-accessor',
    'gen_meta' => $genMeta,
    'gen_proxy' => 'yes',
];
