快速入门
-----------
### 安装
```console
composer require free2one/hyperf-php-accessor
```
### 发布配置
```console
php bin/hyperf.php vendor:publish free2one/hyperf-php-accessor
```
项目`composer.json` 文件中配置以下信息信息
```json
{
    "scripts":{
        "php-accessor": "@php vendor/bin/php-accessor generate"
    }
}
```


### 通过`#[Data]`注解原始类
```php
<?php
namespace App;

use PhpAccessor\Attribute\Data;

#[Data]
class Entity
{
    private int $id;

    private string $name;
}
```
更多注解使用说明详见[PHP Accessor](https://github.com/kkguan/php-accessor).

### PHPStorm插件
<img src="https://plugins.jetbrains.com/files/21172/screenshot_78b22757-36e3-4a90-a405-44acb21c3e10">

建议配合 <a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a> 使用, 该插件支持访问器的跳转,代码提示,查找及类字段重构等.




注意事项
-----------
### 单元测试报错
composer test运行单元测试时可能会出现以下错误信息:
```console
Uncaught Swoole\Error: API must be called in the coroutine in ...../vendor/symfony/console/Terminal.php:156
```
请把原有`bootstrap.php`文件内以下行明细
```php
Swoole\Runtime::enableCoroutine(true);
```
替换为
```php
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL^SWOOLE_HOOK_PROC);
```

### 跳转 or 查找不生效
需要确保`APP_ENV`在本地环境的设置为`dev`,否则请自行修改配置文件`php-accessor.php`中的`genMeta`判断.

```php
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
```


## 相关资源

#### <a href="https://github.com/kkguan/php-accessor">PHP Accessor</a>: 访问器生成器

#### <a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a>: Phpstorm插件,文件保存时自动生成访问器.支持访问器的跳转,代码提示,查找及类字段重构等.

#### <a href="https://github.com/kkguan/hyperf-php-accessor">Hyperf PHP Accessor</a>: Hyperf框架SDK

#### <a href="https://github.com/kkguan/laravel-php-accessor">Laravel PHP Accessor</a>: Laravel框架SDK
