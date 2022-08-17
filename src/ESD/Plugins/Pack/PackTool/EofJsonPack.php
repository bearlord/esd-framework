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
use ESD\Yii\Yii;

/**
 * Class EofJsonPack
 * @package ESD\Plugins\Pack\PackTool
 */
class EofJsonPack extends AbstractPack
{
    use GetLogger;

    /**
     * Packet encode
     *
     * @param $buffer
     * @return string
     */
    public function encode($buffer)
    {
        return $buffer . $this->portConfig->getPackageEof();
    }

    /**
     * Packet decode
     *
     * @param $buffer
     * @return string
     */
    public function decode($buffer)
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
        return $this->encode(json_encode($data, JSON_UNESCAPED_UNICODE));
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
    public function unPack(int $fd, $data, PortConfig $portConfig): ?ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data), true);
        if (empty($value)) {
            $this->warn(Yii::t('esd', 'Packet unpack failed'));
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
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