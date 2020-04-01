<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Request;

/**
 * before拦截器
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class RetryInterceptor extends Interceptor
{
    public function __construct(string $name)
    {
        parent::__construct(Interceptor::RETRY);
    }

    abstract public function handle(Request $request);
}