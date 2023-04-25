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
