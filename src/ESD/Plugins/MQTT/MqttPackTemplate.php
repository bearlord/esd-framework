<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT;

use DI\Annotation\Inject;
use ESD\Core\Exception;
use ESD\Plugins\MQTT\Message\ConnAck;
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
use ESD\Plugins\Redis\RedisException;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\Pack\PackTool\IPack;
use ESD\Plugins\Redis\GetRedis;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\GetUid;
use ESD\Yii\Base\InvalidConfigException;
use ESD\Yii\Yii;

class MqttPackTemplate implements IPack
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
     * @param string $clientId
     * @return string
     */
    public function buildRedisClientKey(string $clientId): string
    {
        return sprintf("MQTT_CLIENT_ID_%s", $clientId);
    }

    /**
     * @param int $fd
     * @return string
     */
    public function buildRedisFdKey(int $fd): string
    {
        return sprintf("MQTT_FD_%s", $fd);
    }

    /**
     * @param $protocolLevel
     * @return object|UnPackV3|UnPackV5
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getUnPackMapInstance($protocolLevel): object
    {
        $mapCLass = $this->unpackMap[$protocolLevel];
        return Yii::createObject($mapCLass);
    }

    /**
     * @param $protocolLevel
     * @return object|ProtocolV3|ProtocolV5
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getProtocolInstance($protocolLevel): object
    {
        $mapClass = $this->protocolMap[$protocolLevel];
        return Yii::createObject($mapClass);
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
     * @throws Exception
     * @throws RedisException
     * @throws InvalidConfigException
     * @throws \RedisException
     */
    public function unPack(int $fd, $data, PortConfig $portConfig): ?ClientData
    {
        $type = UnPackTool::getType($data);

        switch ($type) {
            case Types::CONNECT:
                //协议版本
                $protocolLevel = UnPackTool::getLevel($data);
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);

                $this->redis()->hMSet($this->buildRedisFdKey($fd), [
                    'fd' => $fd,
                    'client_id' => $unpackedData['client_id'],
                    'protocol_level' => $protocolLevel
                ]);

                $this->redis()->hMSet($this->buildRedisClientKey($unpackedData['client_id']), [
                    'fd' => $fd,
                    'client_id' => $unpackedData['client_id'],
                    'protocol_level' => $protocolLevel
                ]);

                $this->autoBoostSend(
                    $fd,
                    (new ConnAck())
                        ->setProtocolLevel($protocolLevel)
                        ->setCode(0)
                        ->setSessionPresent(0)
                );
                break;

            case Types::PUBLISH:
                //协议版本
                $protocolLevel = $this->redis()->hGet($this->buildRedisFdKey($fd), 'protocol_level');
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);

                // Send to subscribers
                $connections = Server::$instance->getConnections();
                foreach ($connections as $sub_fd) {
                    $this->autoBoostSend(
                        $sub_fd,
                        (new Publish())
                            ->setProtocolLevel($protocolLevel)
                            ->setTopic($unpackedData['topic'])
                            ->setMessage($unpackedData['message'])
                            ->setDup($unpackedData['dup'])
                            ->setQos($unpackedData['qos'])
                            ->setRetain($unpackedData['retain'])
                            ->setMessageId($unpackedData['message_id'] ?? 0)
                    );
                }

                switch ($unpackedData['qos'])
                {
                    case 1:
                        $this->autoBoostSend(
                            $fd,
                            (new PubAck())
                                ->setProtocolLevel($protocolLevel)
                                ->setMessageId($unpackedData['message_id'])
                        );
                        break;

                    case 2:
                        $this->autoBoostSend(
                            $fd,
                            (new PubRec())
                                ->setProtocolLevel($protocolLevel)
                                ->setMessageId($unpackedData['message_id'])
                        );
                        break;
                }
                break;

            case Types::PUBACK:
                //协议版本
                $protocolLevel = $this->redis()->hGet($this->buildRedisFdKey($fd), 'protocol_level');
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);
                printf("PUBACK\n");

                var_dump($unpackedData);
                //todo
                break;

            case Types::PUBREL:
                //协议版本
                $protocolLevel = $this->redis()->hGet($this->buildRedisFdKey($fd), 'protocol_level');
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);
                var_dump($unpackedData);

                break;

            case Types::SUBSCRIBE:
                //协议版本
                $protocolLevel = $this->redis()->hGet($this->buildRedisFdKey($fd), 'protocol_level');
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);

                $payload = [];
                foreach ($unpackedData['topics'] as $k => $topic) {
                    if (is_numeric($topic['qos']) && $topic['qos'] < 3) {
                        $payload[] = $topic['qos'];
                    } else {
                        $payload[] = 0x80;
                    }
                }

                $this->autoBoostSend(
                    $fd,
                    (new SubAck())->setProtocolLevel($protocolLevel)
                        ->setMessageId($unpackedData['message_id'] ?? 0)
                        ->setCodes($payload)
                );
                break;

            case Types::UNSUBSCRIBE:
                //协议版本
                $protocolLevel = $this->redis()->hGet($this->buildRedisFdKey($fd), 'protocol_level');
                //解包数据
                $unpackedData = call_user_func([$this->getProtocolInstance($protocolLevel), 'unpack'], $data);

                $this->autoBoostSend(
                    $fd,
                    (new UnSubAck())
                        ->setProtocolLevel($protocolLevel)
                        ->setMessageId($unpackedData['message_id'] ?? 0)
                );
                break;

            case Types::PINGREQ:
                $this->autoBoostSend(
                    $fd,
                    (new PingResp())
                );
                break;

            case Types::DISCONNECT:
                Server::$instance->closeFd($fd);
                break;
        }

        return null;
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