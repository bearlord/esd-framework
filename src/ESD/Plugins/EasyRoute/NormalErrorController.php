<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute;


use ESD\Plugins\EasyRoute\Controller\EasyController;

class NormalErrorController extends EasyController
{

    /**
     * 找不到方法时调用
     * @param $methodName
     * @return mixed
     * @throws RouteException
     */
    protected function defaultMethod(?string $methodName)
    {
        throw new RouteException("404 method $methodName can not find");
    }
}