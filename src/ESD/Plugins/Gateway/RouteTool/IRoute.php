<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\RouteTool;

use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\Pack\ClientData;

/**
 * Interface IRoute
 * @package ESD\Plugins\EasyRoute\RouteTool
 */
interface IRoute
{
    /**
     * @param ClientData $clientData
     * @param EasyRouteConfig $easyRouteConfig
     * @return bool
     */
    public function handleClientData(ClientData $clientData, EasyRouteConfig $easyRouteConfig): bool;

    /**
     * Get Controller name
     *
     * @return mixed
     */
    public function getControllerName();

    /**
     * Get method name
     *
     * @return mixed
     */
    public function getMethodName();

    /**
     * Get params
     *
     * @return mixed
     */
    public function getParams();

    /**
     * Get path
     *
     * @return mixed
     */
    public function getPath();
}