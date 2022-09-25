<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Filter;

use ESD\Plugins\Pack\ClientData;
use ESD\Server\Coroutine\Server;

/**
 * Class ServerFilter
 * @package ESD\Plugins\EasyRoute\Filter
 */
class ServerFilter extends AbstractFilter
{
    /**
     * @return mixed|string
     */
    public function getType()
    {
        return AbstractFilter::FILTER_PRE;
    }

    /**
     * @param ClientData $clientData
     * @return int
     */
    public function filter(ClientData $clientData): int
    {
        $clientData->getResponse()->withHeader('Server', Server::$instance->getServerConfig()->getName());
        return AbstractFilter::RETURN_NEXT;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "ServerFilter";
    }

    /**
     * @param ClientData $clientData
     * @return bool|mixed
     */
    public function isEnable(ClientData $clientData)
    {
        return $this->isHttp($clientData);
    }
}