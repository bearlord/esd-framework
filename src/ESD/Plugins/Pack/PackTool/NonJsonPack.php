<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Config\PortConfig;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Pack\ClientData;
use ESD\Yii\Helpers\Json;
use ESD\Yii\Yii;

/**
 * Class NonJsonPack
 * @package ESD\Plugins\Pack\PackTool
 */
class NonJsonPack implements IPack
{
    use GetLogger;

    /**
     * Packet pack
     *
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return false|string
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        return Json::encode($data);
    }

    /**
     * Packet unpack
     *
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return ClientData
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, $data, PortConfig $portConfig): ?ClientData
    {
        $value = json_decode($data, true);
        if (empty($value)) {
            $this->warn(Yii::t('esd', 'Packet unpack failed'));
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
        return $clientData;
    }

    /**
     * Packet encode
     * @param string $buffer
     */
    public function encode($buffer)
    {
        return;
    }

    /**
     * Packet decode
     *
     * @param string $buffer
     */
    public function decode($buffer)
    {
        return;
    }

    /**
     * Change port config
     *
     * @param PortConfig $portConfig
     * @throws \Exception
     */
    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenWebsocketProtocol()) {
            return true;
        } else {
            Server::$instance->getLog()->warning("NonJsonPack is used but WebSocket protocol is not enabled ,we are automatically turn on WebsocketProtocol for you.");
            $portConfig->setOpenWebsocketProtocol(true);
        }
    }
}