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
use ESD\Plugins\Pack\PackException;

/**
 * Class LenJsonPack
 * @package ESD\Plugins\Pack\PackTool
 */
class LenJsonPack extends AbstractPack
{
    use GetLogger;

    /**
     * Packet pack
     *
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return string
     * @throws PackException
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        $this->portConfig = $portConfig;
        return $this->encode(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Packet unpack
     *
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return ClientData
     * @throws PackException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, $data, PortConfig $portConfig): ?ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data), true);
        if (empty($value)) {
            $this->warn('Packet unpack failed');
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
        return $clientData;
    }

    /**
     * Packet encode
     *
     * @param string $buffer
     * @return string
     * @throws PackException
     */
    public function encode($buffer)
    {
        $totalLength = $this->getLength($this->portConfig->getPackageLengthType()) + strlen($buffer) - $this->portConfig->getPackageBodyOffset();
        return pack($this->portConfig->getPackageLengthType(), $totalLength) . $buffer;
    }

    /**
     * Packet decode
     *
     * @param $buffer
     * @return string
     * @throws PackException
     */
    public function decode($buffer)
    {
        return substr($buffer, $this->getLength($this->portConfig->getPackageLengthType()));
    }


    /**
     * Change port config
     *
     * @param PortConfig $portConfig
     * @return mixed|void
     * @throws \Exception
     */
    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenLengthCheck()) {
            return true;
        } else {
            Server::$instance->getLog()->warning("Packet used LenJsonPack but Length protocol is not enabled, Enable open_length_check automatically");
            $portConfig->setOpenLengthCheck(true);
        }
    }
}