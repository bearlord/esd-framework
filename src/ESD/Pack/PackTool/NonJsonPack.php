<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Plugins\Pack\ClientData;

class NonJsonPack implements IPack
{
    use GetLogger;

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return false|string
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return ClientData
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData
    {
        $value = json_decode($data, true);
        if (empty($value)) {
            $this->warn('json unPack 失败');
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
        return $clientData;
    }

    public function encode(string $buffer)
    {
        return;
    }

    public function decode(string $buffer)
    {
        return;
    }

    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenWebsocketProtocol()) {
            return;
        } else {
            Server::$instance->getLog()->warning("NonJsonPack is used but WebSocket protocol is not enabled ,we are automatically turn on WebsocketProtocol for you.");
            $portConfig->setOpenWebsocketProtocol(true);
        }
    }
}