<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

use DI\Annotation\Inject;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;
use ESD\Plugins\MQTT\Auth\MqttAuth;
use ESD\Plugins\MQTT\Handler\Handler;
use ESD\Plugins\MQTT\IMqtt;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message\Base;
use ESD\Plugins\MQTT\Message\CONNACK;
use ESD\Plugins\MQTT\Message\CONNECT;
use ESD\Plugins\MQTT\Message\PINGRESP;
use ESD\Plugins\MQTT\Message\PUBACK;
use ESD\Plugins\MQTT\Message\PUBCOMP;
use ESD\Plugins\MQTT\Message\PUBLISH;
use ESD\Plugins\MQTT\Message\PUBREC;
use ESD\Plugins\MQTT\Message\PUBREL;
use ESD\Plugins\MQTT\Message\SUBACK;
use ESD\Plugins\MQTT\Message\SUBSCRIBE;
use ESD\Plugins\MQTT\Message\UNSUBACK;
use ESD\Plugins\MQTT\Message\UNSUBSCRIBE;
use ESD\Plugins\MQTT\MQTT;
use ESD\Plugins\MQTT\MqttException;
use ESD\Plugins\MQTT\MqttPluginConfig;
use ESD\Plugins\MQTT\Utility;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\Pack\PackTool\IPack;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\GetUid;

/**
 * Class MqttRoutePack
 * @package ESD\Plugins\MQTT
 */
class MqttRoutePack implements IPack, IMqtt
{
    use GetUid;
    use GetBoostSend;
    use GetTopic;

    /**
     * @Inject()
     * @var MqttAuth
     */
    protected $mqttAuth;

    /**
     * @Inject()
     * @var MqttPluginConfig
     */
    protected $mqttConfig;
    /**
     * @var Handler
     */
    private $handler;

    /**
     * MqttPack constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        Server::$instance->getContainer()->injectOn($this);
        $this->handler = DIGet($this->mqttConfig->getMessageHandleClass());
    }

    /**
     * @param string $buffer
     */
    public function encode(string $buffer)
    {

    }

    /**
     * @param string $buffer
     */
    public function decode(string $buffer)
    {

    }

    /**
     * @param $data
     * @param PortConfig $portConfig
     * @param string|null $topic
     * @return string
     * @throws MqttException
     */
    public function pack($data, PortConfig $portConfig, ?string $topic = null)
    {
        if ($data instanceof Base) {
            $data = $data->build();
        } else {
            $message = new PUBLISH($this);
            $message->setDup(0);
            $message->setQos($this->mqttConfig->getServerQos());
            if ($topic == null && $this->mqttConfig->isUseRoute()) {
                $message->setTopic($this->mqttConfig->getServerTopic() . "/" . getContextValue("uid"));
                $message->setQos(getContextValue("qos"));
                $message->setMsgId(getContextValue("msgId"));
                $data = $this->handler->pack($data);
            } else {
                $message->setTopic($topic);
            }
            $message->setMessage($data);
            $data = $message->build();
        }
        return $data;
    }

    /**
     * @param int $fd
     * @param string $data
     * @param PortConfig $portConfig
     * @return ClientData|null
     * @throws MqttException
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function unPack(int $fd, string $data, PortConfig $portConfig): ?ClientData
    {
        $uid = $this->getFdUid($fd);
        setContextValue("uid", $uid);
        $messageObject = $this->messageRead($data);

        switch ($messageObject->getMessageType()) {
            case Message::CONNECT:
                $connack = new CONNACK($this);
                if ($messageObject instanceof CONNECT) {
                    $connect = $messageObject;
                    if ($connect->getUserNameFlag()) {
                        list($auth, $uid) = $this->mqttAuth->auth($fd, $connect->username, $connect->password);
                        if ($auth) {
                            $this->bindUid($fd, $uid);
                            setContextValue("uid", $uid);
                            $connack->setReturnCode(0);
                            $connack->setSessionPresent(0);
                            $this->autoBoostSend($fd, $connack);
                        } else {
                            $connack->setReturnCode(0x04);
                            $connack->setSessionPresent(0);
                            $this->autoBoostSend($fd, $connack);
                        }
                    } else {
                        if ($this->mqttConfig->isAllowAnonymousAccess()) {
                            if (empty($connect->client_id)) {
                                if ($connect->getClean() == 0) {
                                    $connack->setReturnCode(0x02);
                                    $connack->setSessionPresent(0);
                                    $this->autoBoostSend($fd, $connack);
                                    Server::$instance->closeFd($fd);
                                    break;
                                }
                                $connect->client_id = Utility::genClientId();
                            }
                            $connack->setReturnCode(0);
                            $uid = $connect->client_id;
                            $this->bindUid($fd, $uid);
                            setContextValue("uid", $uid);
                        } else {
                            $connack->setReturnCode(0x05);
                        }
                        $connack->setSessionPresent(0);
                        $this->autoBoostSend($fd, $connack);
                    }
                }
                break;

            case Message::PUBLISH:
                if ($messageObject instanceof PUBLISH) {
                    $publish = $messageObject;
                    $qos = $publish->getQos();
                    $topic = $publish->getTopic();
                    $data = $publish->getMessage();
                    $msgId = $publish->getMsgId();
                    if (!$this->mqttConfig->isUseRoute()) {
                        $this->pub($topic, $data);
                        switch ($qos) {
                            case 1:
                                $puback = new PUBACK($this);
                                $puback->setMsgId($msgId);
                                $this->autoBoostSend($fd, $puback);
                                break;
                            case 2:
                                $pubrec = new PUBREC($this);
                                $pubrec->setMsgId($msgId);
                                $this->autoBoostSend($fd, $pubrec);
                                break;
                        }
                    } else {
                        $clientData = new ClientData($fd, $portConfig->getBaseType(), $topic, $this->handler->upPack($data));
                        setContextValue("msgId", $msgId);
                        setContextValue("qos", $publish->getQos());
                        return $clientData;
                    }
                }
                break;

            case Message::PUBREL:
                if ($messageObject instanceof PUBREL) {
                    $pubrel = $messageObject;
                    $msgId = $pubrel->getMsgId();
                    $pubcomp = new PUBCOMP($this);
                    $pubcomp->setMsgId($msgId);
                    $this->autoBoostSend($fd, $pubcomp);
                }
                break;

            case Message::SUBSCRIBE:
                if ($messageObject instanceof SUBSCRIBE) {
                    $subscribe = $messageObject;
                    $topics = $subscribe->getTopic();
                    $codes = [];
                    foreach ($topics as $topic => $qos) {
                        $codes[] = $qos;
                        $this->addSub($topic, $uid);
                    }
                    $suback = new SUBACK($this);
                    $suback->setMsgId($subscribe->getMsgId());
                    $suback->setReturnCodes($codes);
                    $this->autoBoostSend($fd, $suback);
                }
                break;

            case Message::UNSUBSCRIBE:
                if ($messageObject instanceof UNSUBSCRIBE) {
                    $unsubscribe = $messageObject;
                    $topics = $unsubscribe->getTopic();
                    foreach ($topics as $topic) {
                        $this->removeSub($topic, $uid);
                    }
                    $unsuback = new UNSUBACK($this);
                    $unsuback->setMsgId($unsubscribe->getMsgId());
                    $this->autoBoostSend($fd, $unsuback);
                }
                break;

            case Message::PINGREQ:
                $pingresp = new PINGRESP($this);
                $this->autoBoostSend($fd, $pingresp);
                break;

            case Message::DISCONNECT:
                Server::$instance->closeFd($fd);
                break;
        }
        return null;
    }


    /**
     * Read Message And Create Message Object
     *
     * @param $data
     * @return Base
     * @throws MqttException
     */
    protected function messageRead($data)
    {
        $cmd = Utility::parseCommand(ord($data[0]));
        $messageType = $cmd['message_type'];
        $pos = 1;
        $remainingLength = Utility::decodeLength($data, $pos);
        $messageObject = $this->getMessageObject($messageType);
        $messageObject->decode($data, $remainingLength);
        return $messageObject;
    }

    /**
     * Create Message\Base object
     *
     * @param int $messageType
     * @return Message\Base
     * @throws MqttException
     */
    public function getMessageObject($messageType)
    {
        return Message::create($messageType, $this);
    }

    /**
     * @return int|mixed
     */
    public function version()
    {
        return MQTT::VERSION_3_1_1;
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
            Server::$instance->getLog()->warning("MqttPack is used but Mqtt protocol is not enabled ,we are automatically turn on MqttProtocol for you.");
            $portConfig->setOpenMqttProtocol(true);
        }
    }

}