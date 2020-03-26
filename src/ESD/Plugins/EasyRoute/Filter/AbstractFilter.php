<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

use ESD\Core\Order\Order;
use ESD\Plugins\Pack\ClientData;

/**
 * 中间件
 * Class AbstractFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
abstract class AbstractFilter extends Order
{
    const FILTER_PRE = "filter_pre";
    const FILTER_PRO = "filter_pro";
    const FILTER_ROUTE = "filter_route";
    /**
     * 执行下一个
     */
    const RETURN_NEXT = 0;
    /**
     * 结束filter进程
     */
    const RETURN_END_FILTER = -1;
    /**
     * 终止路由进程
     */
    const RETURN_END_ROUTE = -2;

    abstract public function isEnable(ClientData $clientData);

    abstract public function getType();

    abstract public function filter(ClientData $clientData): int;

    public function isHttp(ClientData $clientData): bool
    {
        return $clientData->getResponse() !== null && $clientData->getRequest() != null;
    }
}