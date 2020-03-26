<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Plugins\Pack\ClientData;
use ESD\Server\Co\Server;

class ServerFilter extends AbstractFilter
{

    public function getType()
    {
        return AbstractFilter::FILTER_PRE;
    }

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

    public function isEnable(ClientData $clientData)
    {
        return $this->isHttp($clientData);
    }
}