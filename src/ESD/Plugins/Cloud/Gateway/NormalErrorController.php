<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway;

use ESD\Plugins\EasyRoute\Controller\EasyController;

/**
 * Class NormalErrorController
 * @package ESD\Plugins\EasyRoute
 */
class NormalErrorController extends EasyController
{

    /**
     * Called when no method is found
     *
     * @param $methodName
     * @return mixed
     * @throws RouteException
     */
    protected function defaultMethod(?string $methodName)
    {
        throw new RouteException("404 method $methodName can not find");
    }
}