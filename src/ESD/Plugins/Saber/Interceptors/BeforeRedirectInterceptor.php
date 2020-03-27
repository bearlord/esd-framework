<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 9:29
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Request;
use Swlib\Saber\Response;

/**
 * before拦截器
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class BeforeRedirectInterceptor extends Interceptor
{
    public function __construct(string $name)
    {
        parent::__construct(Interceptor::BEFORE_REDIRECT);
    }

    abstract public function handle(Request $request,Response $response);
}