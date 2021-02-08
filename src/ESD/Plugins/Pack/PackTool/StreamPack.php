<?php

/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 * @author Bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Pack\PackTool;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Plugins\Pack\ClientData;
use ESD\Yii\Helpers\Json;
use ESD\Yii\Yii;

/**
 * Class StreamPack
 * @package ESD\Plugins\Pack\PackTool
 */
class StreamPack extends AbstractPack
{
    use GetLogger;

    /**
     * Packet encode
     *
     * @param $buffer
     * @return string
     */
    public function encode(string $buffer)
    {
        return $buffer . $this->portConfig->getPackageEof();
    }

    /**
     * Packet decode
     *
     * @param $buffer
     * @return string
     */
    public function decode(string $buffer)
    {
        $data = str_replace($this->portConfig->getPackageEof(), '', $buffer);
        return $data;
    }

    /**
     * Data pack
     *
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return string
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode($data);
    }

    /**
     * Packet unpack
     *
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData
    {
        $this->portConfig = $portConfig;
        //Value can be empty
        $value = $this->decode($data);
        $clientData = new ClientData($fd, $portConfig->getBaseType(), 'onReceive', $value);
        return $clientData;
    }

    /**
     * Change port config
     *
     * @param PortConfig $portConfig
     * @throws \Exception
     */
    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenEofCheck() || $portConfig->isOpenEofSplit()) {
            return true;
        } else {
            Server::$instance->getLog()->warning(Yii::t('esd', 'Packet used EofJsonPack but EOF protocol is not enabled, Enable open_eof_split automatically'));
            $portConfig->setOpenEofSplit(true);
        }
    }
}