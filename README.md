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


配置项说明
-----------
### `is_dev_mode`
* true: 每次启动时重新生成访问器代理类.
* false: 启动时检测代理目录是否存在,存在则不再重新生成.

### `proxy_root_directory`
访问器代理类存放目录,默认为`.php-accessor`目录.

### `log_level`
日志级别,默认为`debug`.

### `max_concurrent_processes`
最大并行进程数,默认为`2`.

### `max_files_per_process`
进程内最大处理文件数,默认为`200`.

注意事项
-----------

### 跳转 or 查找不生效
本地开发请务必打开`is_dev_mode`配置项, 否则<a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a>将无法正常使用.

```php
<?php

declare(strict_types=1);

use Psr\Log\LogLevel;

return [
    'is_dev_mode' => true, // 是否开启开发模式,本地开发时建议开启,否则无法配合ide插件使用
    'proxy_root_directory' => '.php-accessor',
    'log_level' => LogLevel::DEBUG,
    'max_concurrent_processes' => 2,  
    'max_files_per_process' => 200,   
];
```


## 相关资源

#### <a href="https://github.com/kkguan/php-accessor">PHP Accessor</a>: 访问器生成器

#### <a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a>: Phpstorm插件,文件保存时自动生成访问器.支持访问器的跳转,代码提示,查找及类字段重构等.

#### <a href="https://github.com/kkguan/hyperf-php-accessor">Hyperf PHP Accessor</a>: Hyperf框架SDK

#### <a href="https://github.com/kkguan/laravel-php-accessor">Laravel PHP Accessor</a>: Laravel框架SDK
