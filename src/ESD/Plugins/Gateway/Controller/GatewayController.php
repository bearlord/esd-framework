<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Controller;

use ESD\Plugins\EasyRoute\Controller\EasyController;


class GatewayController extends EasyController
{

    protected function defaultMethod(?string $methodName)
    {
        var_dump("aaa");
    }

    public function onExceptionHandle(\Throwable $exception)
    {
        var_dump("aaa");
    }

}