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

class EofJsonPack extends AbstractPack
{
    use GetLogger;
    protected $last_data = null;
    protected $last_data_result = null;

    /**
     * 数据包编码
     * @param $buffer
     * @return string
     */
    public function encode(string $buffer)
    {
        return $buffer . $this->portConfig->getPackageEof();
    }

    /**
     * 数据包解码
     * @param $buffer
     * @return string
     */
    public function decode(string $buffer)
    {
        $data = str_replace($this->portConfig->getPackageEof(), '', $buffer);
        return $data;
    }

    /**
     * 数据包打包
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
     * 数据包解包
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData
    {
        $this->portConfig = $portConfig;
        $value = json_decode($this->decode($data), true);
        if (empty($value)) {
            $this->warn('json unPack fail');
            return null;
        }
        $clientData = new ClientData($fd, $portConfig->getBaseType(), $value['p'], $value);
        return $clientData;
    }

    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenEofCheck() || $portConfig->isOpenEofSplit()) {
            return;
        } else {
            Server::$instance->getLog()->warning("EofJsonPack is used but EOF protocol is not enabled ,we are automatically turn on EofSplit for you.");
            $portConfig->setOpenEofSplit(true);
        }
    }
}