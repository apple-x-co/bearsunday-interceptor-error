# bearsunday-interceptor-error

## 現象

Module 内で `bindInterceptor()` の 第三パラメータの `$interceptors` に2つのクラスを渡すと以下エラーが発生する

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
