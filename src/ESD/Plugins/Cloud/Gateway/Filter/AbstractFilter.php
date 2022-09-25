<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Filter;

use ESD\Core\Order\Order;
use ESD\Plugins\Pack\ClientData;

/**
 * Filter Middleware
 * Class AbstractFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
abstract class AbstractFilter extends Order
{
    const FILTER_PRE = "filter_pre";
    const FILTER_PRO = "filter_pro";
    const FILTER_ROUTE = "filter_route";

    /**
     * Return next
     */
    const RETURN_NEXT = 0;

    /**
     * Return end filter
     */
    const RETURN_END_FILTER = -1;

    /**
     * Return end route
     */
    const RETURN_END_ROUTE = -2;

    /**
     * @param ClientData $clientData
     * @return mixed
     */
    abstract public function isEnable(ClientData $clientData);

    /**
     * @return mixed
     */
    abstract public function getType();

    /**
     * @param ClientData $clientData
     * @return int
     */
    abstract public function filter(ClientData $clientData): int;

    /**
     * @param ClientData $clientData
     * @return bool
     */
    public function isHttp(ClientData $clientData): bool
    {
        return $clientData->getResponse() !== null && $clientData->getRequest() != null;
    }
}