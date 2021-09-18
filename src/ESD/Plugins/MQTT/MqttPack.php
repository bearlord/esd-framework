<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT;

use DI\Annotation\Inject;
use ESD\Plugins\MQTT\Message\AbstractMessage;
use ESD\Plugins\MQTT\Message\ConnAck;
use ESD\Plugins\MQTT\Message\DisConnect;
use ESD\Plugins\MQTT\Message\PingResp;
use ESD\Plugins\MQTT\Message\Publish;
use ESD\Plugins\MQTT\Message\PubRec;
use ESD\Plugins\MQTT\Message\SubAck;
use ESD\Plugins\MQTT\Message\UnSubAck;
use ESD\Plugins\MQTT\Packet\PackV3;
use ESD\Plugins\MQTT\Packet\PackV5;
use ESD\Plugins\MQTT\Packet\UnPackV3;
use ESD\Plugins\MQTT\Packet\UnPackV5;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;
use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Tools\UnPackTool;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Plugins\MQTT\MqttPluginConfig;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\Pack\PackTool\AbstractPack;
use ESD\Plugins\Pack\PackTool\IPack;
use ESD\Plugins\Redis\GetRedis;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\GetUid;
use ESD\Yii\Yii;
use function DI\get;

class MqttPack extends AbstractPack
{
    use GetUid;
    use GetBoostSend;
    use GetTopic;
    use GetRedis;

    /**
     * @var array
     */
    protected $packMap = [
        3 => PackV3::class,
        4 => PackV3::class,
        5 => PackV5::class
    ];

    /**
     * @var array
     */
    protected $unpackMap = [
        3 => UnPackV3::class,
        4 => UnPackV3::class,
        5 => UnPackV5::class
    ];

    /**
     * @var array
     */
    protected $protocolMap = [
        3 => ProtocolV3::class,
        4 => ProtocolV3::class,
        5 => ProtocolV5::class
    ];


    /**
     * MqttPack constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        Server::$instance->getContainer()->injectOn($this);
    }

    /**
     * 保存客户端协议版本到上下文
     *
     * @param int $fd
     * @param int $protocolLevel
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function setFdProtocolLevel(int $fd, int $protocolLevel): void
    {
        $key = sprintf("MQTT_FD_PROTOCOL_LEVEL_%s", $fd);

        setContextValue($key, $protocolLevel);
        $this->redis()->set($key, $protocolLevel);
    }

    /**
     * 保存客户端协议版本到上下文
     *
     * @param int $fd
     * @return int|null
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function getFdProtocolLevel(int $fd): ?int
    {
        $key = sprintf("MQTT_FD_PROTOCOL_LEVEL_%s", $fd);
        $value = getContextValue($key);

        if (!$value) {
            $value = $this->redis()->get($key);
            setContextValue($key, $value);
        }

        return $value;
    }

    /**
     * @param $protocolLevel
     * @return object|ProtocolV3|ProtocolV5
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    protected function getProtocolInstance($protocolLevel): object
    {
        $mapClass = $this->protocolMap[$protocolLevel];
        return Yii::createObject($mapClass);
    }

    /**
     * 保存 Fd 和 ClientId关系，fd是key
     *
     * @param int $fd
     * @param string $clientId
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function setFdClientIdMap(int $fd, string $clientId): void
    {
        $key = sprintf("MQTT_Fd_ClientId_Map_%s", $fd);

        setContextValue($key, $clientId);
        $this->redis()->set($key, $clientId);
    }

    /**
     * 保存 ClientId 和 Fd关系，clientId是key
     *
     * @param string $clientId
     * @param int $fd
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function setClientIdFdMap(string $clientId, int $fd): void
    {
        $key = sprintf("MQTT_ClientId_Fd_Map_%s", $clientId);

        setContextValue($key, $fd);
        $this->redis()->set($key, $fd);
    }

    /**
     * 根据 Fd 获取 ClientId
     *
     * @param int $fd
     * @return false|mixed|string
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function getClientIdFromFd(int $fd)
    {
        $key = sprintf("MQTT_Fd_ClientId_Map_%s", $fd);
        $value = getContextValue($key);
        if (!$value) {
            $value = $this->redis()->get($key);
            setContextValue($key, $value);
        }
        return $value;
    }

    /**
     * 根据 ClientId 获取 Fd
     *
     * @param string $clientId
     * @return false|mixed|string
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function getFdFromClientId(string $clientId)
    {
        $key = sprintf("MQTT_ClientId_Fd_Map_%s", $clientId);
        $value = getContextValue($key);
        if (!$value) {
            $value = $this->redis()->get($key);
            setContextValue($key, $value);
        }
        return $value;
    }

    /**
     * @param $clientId
     * @param $data
     * @throws \ESD\Plugins\Redis\RedisException
     */
    protected function setClientConnectionInfo($clientId, $data)
    {
        $key = sprintf("MQTT_CLIENT_CONNECTION_%s", $clientId);

        setContextValue($key, $data);
        $this->redis()->hMSet($key, $data);
    }

    /**
     * @param string $buffer
     */
    public function encode($buffer)
    {
        return $buffer;
    }

    /**
     * @param string $buffer
     */
    public function decode($buffer)
    {
        return $buffer;
    }

    /**
     * @param mixed $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return mixed
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        return $data;
    }

    /**
     * @param int $fd
     * @param mixed $data
     * @param PortConfig $portConfig
     * @return ClientData|null
     * @throws \ESD\Plugins\Redis\RedisException
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function unPack(int $fd, $data, PortConfig $portConfig): ?ClientData
    {
        $type = UnPackTool::getType($data);

        switch ($type) {
            case Types::CONNECT:
                //协议版本
                $protocolLevel = UnPackTool::getProtocolLevel($data);
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);
                //客户端标识
                $clientId = $unpackedData['client_id'];
                //保存协议到上下文
                $this->setFdProtocolLevel($fd, $protocolLevel);
                //保存 fd 和 clientId 映射关系
                $this->setFdClientIdMap($fd, $clientId);
                //保存 clientId 和 fd 映射关系
                $this->setClientIdFdMap($clientId, $fd);
                //保存客户端的连接信息
                $this->setClientConnectionInfo($clientId, $unpackedData);

                break;

            default:
                //协议版本
                $protocolLevel = $this->getFdProtocolLevel($fd);
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);
                //客户端标识
                $clientId = $this->getClientIdFromFd($fd);
        }

        return new ClientData($fd, $portConfig->getBaseType(), 'onReceive', [
            'type' => $type,
            'level' => $protocolLevel,
            'client_id' => $clientId,
            'data' => $unpackedData
        ]);
    }

    /**
     * @param PortConfig $portConfig
     * @throws \Exception
     */
    public static function changePortConfig(PortConfig $portConfig)
    {
        if ($portConfig->isOpenMqttProtocol()) {
            return;
        } else {
            Server::$instance->getLog()->warning("MqttPack is used but MQTT protocol is not enabled ,we are automatically turn on MqttProtocol for you.");
            $portConfig->setOpenMqttProtocol(true);
        }
    }
}