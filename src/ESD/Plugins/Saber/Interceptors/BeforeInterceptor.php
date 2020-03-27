<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 9:29
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Request;

/**
 * before拦截器
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class BeforeInterceptor extends Interceptor
{
    public function __construct()
    {
        parent::__construct(Interceptor::BEFORE);
    }

    abstract public function handle(Request $request);
}