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


### 通过`#[HyperfData]`注解原始类
除了PHP Accessor原有的注解外,需要额外使用该注解来标识其可被Hyperf<a href="https://hyperf.wiki/3.0/#/zh-cn/annotation?id=%e8%87%aa%e5%ae%9a%e4%b9%89%e6%b3%a8%e8%a7%a3">收集</a>.
```php
<?php
namespace App;

use Hyperf\PhpAccessor\Annotation\HyperfData;
use PhpAccessor\Attribute\Data;

#[HyperfData]
#[Data]
class Entity
{
    private int $id;

    private string $name;
}
```


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

$appEnv = env('APP_ENV', 'dev');
$genMeta = $appEnv == 'dev' ? 'yes' : 'no';

return [
    'proxy_root_directory' => BASE_PATH . DIRECTORY_SEPARATOR . '.php-accessor',
    'gen_meta' => $genMeta,
    'gen_proxy' => 'yes',
];

```


## 相关资源

#### <a href="https://github.com/kkguan/php-accessor">PHP Accessor</a>: 生成类访问器（Getter & Setter）
#### <a href="https://github.com/kkguan/php-accessor-idea-plugin">PHP Accessor IDEA Plugin</a>: Phpstorm辅助插件,文件保存时自动生成访问器.支持访问器的跳转,代码提示,查找及类字段重构等.



