# bearsunday-interceptor-error

## 現象

Module 内で `bindInterceptor()` の 第三パラメータの `$interceptors` に2つ以上のクラスを設定すると `Ray\Di\Exception\Untargeted` エラーが発生する

```text
500 Internal Server Error
content-type: application/vnd.error+json

{
    "message": "Internal Server Error",
    "logref": "e812e2fa",
    "request": "get page://self/index",
    "exceptions": "Ray\\Di\\Exception\\Untargeted(MyVendor\\MyProject\\Interceptor\\BInterceptor)",
    "file": "/path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php(140)"
}
```

```text
Tue, 25 Apr 2023 06:46:25 +0000
get page://self/index

Ray\Di\Exception\Untargeted(MyVendor\MyProject\Interceptor\BInterceptor)
 in file /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php on line 140

#0 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php(108): Ray\Di\Container->unbound('MyVendor\\MyProj...')
#1 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php(71): Ray\Di\Container->getDependency('MyVendor\\MyProj...')
#2 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/AspectBind.php(36): Ray\Di\Container->getInstance('MyVendor\\MyProj...')
#3 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/NewInstance.php(90): Ray\Di\AspectBind->inject(Object(Ray\Di\Container))
#4 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/NewInstance.php(53): Ray\Di\NewInstance->postNewInstance(Object(Ray\Di\Container), Object(MyVendor\MyProject\Resource\Page\Index_2938426659))
#5 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Dependency.php(75): Ray\Di\NewInstance->__invoke(Object(Ray\Di\Container))
#6 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php(111): Ray\Di\Dependency->inject(Object(Ray\Di\Container))
#7 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Container.php(71): Ray\Di\Container->getDependency('MyVendor\\MyProj...')
#8 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Injector.php(84): Ray\Di\Container->getInstance('MyVendor\\MyProj...')
#9 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/ray/di/src/di/Injector.php(65): Ray\Di\Injector->bind('MyVendor\\MyProj...')
#10 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/bear/resource/src/AppAdapter.php(47): Ray\Di\Injector->getInstance('MyVendor\\MyProj...')
#11 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/bear/resource/src/Factory.php(42): BEAR\Resource\AppAdapter->get(Object(BEAR\Resource\Uri))
#12 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/bear/resource/src/Resource.php(60): BEAR\Resource\Factory->newInstance(Object(BEAR\Resource\Uri))
#13 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/vendor/bear/resource/src/Resource.php(80): BEAR\Resource\Resource->newInstance(Object(BEAR\Resource\Uri))
#14 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/src/Bootstrap.php(39): BEAR\Resource\Resource->uri('page://self/ind...')
#15 /path/to/repository/apple-x-co/bearsunday-interceptor-error/MyVendor.MyProject/bin/page.php(8): MyVendor\MyProject\Bootstrap->__invoke('cli-hal-app', Array, Array)
#16 {main}
```

## 再現手順

1. プロジェクトを `composer create-project -n bear/skeleton MyVendor.MyProject` で作成する
2. `php bin/page.php get /index` で動くことを確認する
3. `AInterceptor` と `BInterceptor` を用意し、`AppModule` で設定をする
4. `php bin/page.php get /index` でエラーが発生する
5. `AInterceptor` または `BInterceptor` のみを設定した場合は動くことが確認できる


### MyVendor\MyProject\Interceptor\AInterceptor

```php
<?php

namespace MyVendor\MyProject\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class AInterceptor implements MethodInterceptor
{
	public function invoke(MethodInvocation $invocation): mixed
	{
		var_dump(__METHOD__);

		return $invocation->proceed();
	}
}
```

### MyVendor\MyProject\Interceptor\BInterceptor

```php
<?php

namespace MyVendor\MyProject\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

class BInterceptor implements MethodInterceptor
{
	public function invoke(MethodInvocation $invocation): mixed
	{
		var_dump(__METHOD__);

		return $invocation->proceed();
	}
}
```

### MyVendor\MyProject\Module\AppModule

```php
<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Module;

use BEAR\Dotenv\Dotenv;
use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Resource\ResourceObject;
use MyVendor\MyProject\Interceptor\AInterceptor;
use MyVendor\MyProject\Interceptor\BInterceptor;

use function dirname;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new Dotenv())->load(dirname(__DIR__, 2));
        $this->install(new PackageModule());

        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->startsWith('on'),
            [AInterceptor::class, BInterceptor::class],
        );
    }
}
```
