<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

use ESD\Plugins\MQTT\Exception\ConnectError;
use ESD\Plugins\MQTT\Exception\NetworkError;
use ESD\Plugins\MQTT\Message\Base;
use ESD\Plugins\MQTT\Message\Will;

/**
 * Class MQTT
 *
 * @package sskaje\mqtt
 */
class MQTT implements IMqtt
{
    /**
     * Client ID
     *
     * @var null|string
     */
    public $clientid;

    /**
     * Socket Connection
     *
     * @var SocketClient
     */
    protected $socket;

    /**
     * Keep Alive Time
     * @var int
     */
    protected $keepalive = 60;

    /**
     * Connect Username
     *
     * @var string
     */
    protected $username = '';

    /**
     * Connect Password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Connect Clean
     *
     * @var bool
     */
    protected $connectClean = true;

    /**
     * Connect Will
     *
     * @var Will
     */
    protected $connectWill;

    /**
     * Version Code
     */
    const VERSION_3 = 3;
    const VERSION_3_0 = 3;
    const VERSION_3_1 = 3;
    const VERSION_3_1_1 = 4;

    /**
     * Current version
     *
     * Default: MQTT 3.0
     *
     * @var int
     */
    protected $version = self::VERSION_3_0;

    /**
     * Unix Timestamp
     *
     * @var int
     */
    protected $connectedTime = 0;

    /**
     * Message Handler
     *
     * @var MessageHandler
     */
    protected $handler = null;

    /**
     * @var CMDStore
     */
    protected $cmdStore = null;

    /**
     * Retry Timeout
     *
     * @var int
     */
    protected $retryTimeout = 5;

    /**
     * Constructor
     *
     * @param string $address
     * @param null|string $clientid
     * @throws \ESD\Plugins\MQTT\MqttException
     */
    public function __construct($address, $clientid = null)
    {
        # Create Socket Client Object
        $this->socket = new SocketClient($address);
        # New Command Store
        $this->cmdStore = new CMDStore();

        # Check Client ID
        Utility::checkClientId($clientid);

        $this->clientid = $clientid;
    }

    /**
     * Retry Timeout for PUBLISH and Following Commands
     *
     * @param int $retryTimeout
     */
    public function setRetryTimeout($retryTimeout)
    {
        if ($retryTimeout > 1) {
            $this->retryTimeout = (int)$retryTimeout;
        }
    }

    /**
     * Create Message\Base object
     *
     * @param int $messageType
     * @return Message\Base
     * @throws \ESD\Plugins\MQTT\MqttException
     */
    public function getMessageObject($messageType)
    {
        return Message::Create($messageType, $this);
    }

    /**
     * Set Protocol Version
     *
     * @param string $version
     * @throws MqttException
     */
    public function setVersion($version)
    {
        if ($version == self::VERSION_3 || $version == self::VERSION_3_1_1) {
            $this->version = $version;
        } else {
            throw new MqttException('Invalid version');
        }
    }

    /**
     * Get Protocol Version
     *
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * Set username/password
     *
     * @param string $username
     * @param string $password
     */
    public function setAuth($username = null, $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Set Keep Alive timer
     *
     * @param int $keepalive
     */
    public function setKeepalive($keepalive)
    {
        $this->keepalive = (int)$keepalive;
    }

    /**
     * Set Clean Session
     *
     * @param bool $clean
     */
    public function setConnectClean($clean)
    {
        $this->connectClean = $clean ? true : false;
    }

    /**
     * Set Will Message
     *
     * @param string $topic
     * @param string $message
     * @param int $qos 0,1,2
     * @param int $retain bool
     * @throws MqttException
     */
    public function setWill($topic, $message, $qos = 0, $retain = 0)
    {
        $this->connectWill = new Will($topic, $message, $qos, $retain);
    }

    /**
     * Stream Context
     *
     * @param resource $context
     * @link  http://php.net/manual/en/context.php
     */
    public function setSocketContext($context)
    {
        $this->socket->setContext($context);
    }

    /**
     * Set Message Handler
     *
     * @param MessageHandler $handler
     */
    public function setHandler(MessageHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Invoke Functions in Message Handler
     *
     * @param string $name
     * @param array $params
     * @return bool
     */
    protected function callHandler($name, array $params = array())
    {
        if ($this->handler === null) {
            return false;
        }

        if (!is_callable(array($this->handler, $name))) {
            Debug::log(Debug::ERR, "callHandler function {$name} NOT CALLABLE");
            return false;
        }

        call_user_func_array(array($this->handler, $name), $params);
        return true;
    }

    /**
     * Create Packet Identifier Generator
     *
     * @return PacketIdentifier
     */
    public function PIG()
    {
        $pi = new PacketIdentifier();

        # set msg id
        $pi->set(mt_rand(1, 65535));

        return $pi;
    }

    public function connectAndLoop()
    {
        $this->_connect();
        \Swoole\Coroutine::create(function () {
            $this->_loop();
        });
    }

    /**
     * Connect to broker
     * s*
     * @return Message\CONNACK
     * @throws MqttException
     */
    protected function _connect()
    {
        /*
         The Server MUST process a second CONNECT Packet sent from a Client as a protocol violation
         and disconnect the Client [MQTT-3.1.0-2]

         So CONNECT SHOULD be a blocking connection.
         */

        $connect_retry_count = 0;
        $max_connect_retry_count = 10;
        do {
            $r = $this->socket->connect();
            if ($r) {
                break;
            }

            if (++$connect_retry_count > $max_connect_retry_count) {
                throw new ConnectError('Failed to connect to ' . $this->socket->getAddress());
            }
        } while (true);

        Debug::log(Debug::INFO, 'connect(): Connection established.');

        $this->socket->set_blocking();

        /**
         * @var Message\CONNECT $connectobj
         */
        $connectobj = $this->getMessageObject(Message::CONNECT);

        if (!$this->connectClean && empty($this->clientid)) {
            throw new MqttException('Client id must be provided if Clean Session flag is set false.');
        }

        # default client id
        if (empty($this->clientid)) {
            $this->clientid = Utility::genClientId();
        }
        $connectobj->setClientId($this->clientid);
        Debug::log(Debug::DEBUG, 'connect(): clientid=' . $this->clientid);

        $connectobj->setKeepalive($this->keepalive);
        Debug::log(Debug::DEBUG, 'connect(): keepalive=' . $this->keepalive);

        $connectobj->setAuth($this->username, $this->password);
        Debug::log(Debug::DEBUG, 'connect(): username=' . $this->username . ' password=' . $this->password);

        $connectobj->setClean($this->connectClean);

        if ($this->connectWill instanceof Will) {
            $connectobj->setWill($this->connectWill);
        }

        $length = 0;

        $bytes_written = $this->messageWrite($connectobj, $length);
        Debug::log(Debug::DEBUG, 'connect(): bytes written=' . $bytes_written);

        /**
         * @var Message\CONNACK $connackobj
         */
        $connackobj = null;

        $connackobj = $this->messageRead();

        Debug::log(Debug::INFO, 'connect(): connected=' . ($connackobj->getMessageType() == Message::CONNACK ? 1 : 0));

        # save current time for ping
        $this->connectedTime = time();

        # Call connect
        $this->callHandler('connack', array($this, $connackobj));
        return $connackobj;
    }

    /**
     * Disconnect connection
     *
     * @return bool
     * @throws MqttException
     */
    public function disconnect()
    {
        Debug::log(Debug::INFO, 'disconnect()');

        $this->simpleCommand(Message::DISCONNECT);

        /*
         After sending a DISCONNECT Packet the Client:
         MUST close the Network Connection [MQTT-3.14.4-1].
         MUST NOT send any more Control Packets on that Network Connection [MQTT-3.14.4-2].
         */
        $this->socket->close();

        $this->callHandler('disconnect', array($this));

        return true;
    }

    /**
     * Reconnect connection
     *
     * @param bool $close_current close current existed connection
     * @return Message\CONNACK
     * @throws MqttException
     */
    public function reconnect($close_current = true)
    {
        Debug::log(Debug::INFO, 'reconnect()');
        if ($close_current) {
            Debug::log(Debug::DEBUG, 'reconnect(): close current');
            $this->disconnect();
            $this->socket->close();
        }
        $this->count_eof = 0;
        return $this->_connect();
    }

    /**
     * Publish Message to topic
     *
     * @param string $topic
     * @param string $message
     * @param int $qos
     * @param int $retain
     * @param null $msgId
     * @return array|bool
     * @throws MqttException
     */
    public function publish($topic, $message, $qos = 0, $retain = 0, &$msgId = null)
    {
        # non blocking
        $this->socket->set_non_blocking();

        # set dup 0
        $dup = 0;

        # initial msgid = 0
        $msgId = 0;

        return $this->doPublish($topic, $message, $qos, $retain, $msgId, $dup);
    }

    /**
     * Publish Message to topic
     *
     * @param string $topic
     * @param string $message
     * @param int $qos Optional, QoS, Default to 0
     * @param int $retain Optional, RETAIN, Default to 0
     * @param int|null & $msgId Optional, Packet Identifier
     * @param int $dup Optional, Default to 0
     * @return array|bool
     * @throws MqttException
     */
    protected function doPublish($topic, $message, $qos = 0, $retain = 0, &$msgId = 0, $dup = 0)
    {
        /**
         * @var PacketIdentifier[] $pis
         */
        static $pis = array();

        if ($qos) {
            if (!isset($pis[$qos])) {
                $pis[$qos] = $this->PIG();
            }

            if (!$msgId) {
                $msgId = $pis[$qos]->next();
            }
        }

        Debug::log(Debug::INFO, "publish() QoS={$qos}, MsgId={$msgId}, DUP={$dup}");
        /**
         * @var Message\PUBLISH $publishobj
         */
        $publishobj = $this->getMessageObject(Message::PUBLISH);

        $publishobj->setTopic($topic);
        $publishobj->setMessage($message);

        $publishobj->setDup($dup);
        $publishobj->setQos($qos);
        $publishobj->setRetain($retain);

        $publishobj->setMsgId($msgId);

        $publish_bytes_written = $this->messageWrite($publishobj);
        Debug::log(Debug::DEBUG, 'doPublish(): bytes written=' . $publish_bytes_written);

        if ($qos == 1) {
            # QoS = 1, PUBLISH + PUBACK
            if (!$dup) {
                $this->cmdStore->addWait(
                    Message::PUBACK,
                    $msgId,
                    array(
                        'msgid' => $msgId,
                        'retry' => array(
                            'retain' => $retain,
                            'topic' => $topic,
                            'message' => $message,
                        ),
                        'retry_after' => time() + $this->retryTimeout,
                    )
                );
            }
        } else if ($qos == 2) {
            # QoS = 2, PUBLISH + PUBREC + PUBREL + PUBCOMP
            if (!$dup) {
                $this->cmdStore->addWait(
                    Message::PUBREC,
                    $msgId,
                    array(
                        'msgid' => $msgId,
                        'retry' => array(
                            'retain' => $retain,
                            'topic' => $topic,
                            'message' => $message,
                        ),
                        'retry_after' => time() + $this->retryTimeout,
                    )
                );
            }
        }

        return array(
            'qos' => $qos,
            'ret' => $publish_bytes_written != false,
            'publish' => $publish_bytes_written,
            'msgid' => $msgId,
        );
    }

    /**
     * Currently Subscribed Topics (Topic Filter)
     *
     * @var array
     */
    protected $topics = array();

    /**
     * Topics to Subscribe (Topic Filter)
     *
     * @var array
     */
    protected $topicsToSubscribe = array();

    /**
     * Topics to Unsubscribe (Topic Filter)
     *
     * @var array
     */
    protected $topicsToUnsubscribe = array();

    /**
     * $topics['mqtttest/#'] = 2;
     * $mqtt2->subscribe($topics);
     * SUBSCRIBE
     *
     * @param array $topics array($topic_filter => $topic_qos)
     * @return bool
     */
    public function subscribe(array $topics)
    {
        foreach ($topics as $topic_filter => $topic_qos) {
            $this->topicsToSubscribe[$topic_filter] = $topic_qos;
        }
        return true;
    }

    /**
     * UNSUBSCRIBE
     *
     * @param array $topics Topic Filters
     * @return bool
     */
    public function unsubscribe(array $topics)
    {
        foreach ($topics as $topic_filter) {
            $this->topicsToUnsubscribe[] = $topic_filter;
        }
        return true;
    }

    /**
     * DO SUBSCRIBE
     *
     * @return array (msgid, topic qos)
     * @throws MqttException
     */
    protected function doSubscribe()
    {
        /**
         * Packet Identifier Generator
         *
         * @var PacketIdentifier $pi
         */
        static $pi = null;
        if (!$pi) {
            $pi = $this->PIG();
        }

        $msgId = $pi->next();

        # send SUBSCRIBE

        /**
         * @var Message\SUBSCRIBE $subscribeobj
         */
        $subscribeobj = $this->getMessageObject(Message::SUBSCRIBE);
        $subscribeobj->setMsgId($msgId);

        $all_topic_qos = array();
        foreach ($this->topicsToSubscribe as $topic_filter => $topic_qos) {
            $subscribeobj->addTopic(
                $topic_filter,
                $topic_qos
            );

            $all_topic_qos[] = array($topic_filter, $topic_qos);
            unset($this->topicsToSubscribe[$topic_filter]);
        }

        Debug::log(Debug::DEBUG, 'doSubscribe(): msgid=' . $msgId);
        $subscribe_bytes_written = $this->messageWrite($subscribeobj);
        Debug::log(Debug::DEBUG, 'doSubscribe(): bytes written=' . $subscribe_bytes_written);

        # The Server is permitted to start sending PUBLISH packets matching the Subscription before the Server sends the SUBACK Packet.
        # No SUBACK processing here, go to loop()

        return array($msgId, $all_topic_qos);
    }

    /**
     * DO Unsubscribe topics
     *
     * @return array(int, array)
     * @throws MqttException
     */
    protected function doUnsubscribe()
    {
        /**
         * Packet Identifier Generator
         *
         * @var PacketIdentifier $pi
         */
        static $pi = null;
        if (!$pi) {
            $pi = $this->PIG();
        }

        $msgId = $pi->next();

        # send SUBSCRIBE
        /**
         * @var Message\UNSUBSCRIBE $unsubscribeobj
         */
        $unsubscribeobj = $this->getMessageObject(Message::UNSUBSCRIBE);
        $unsubscribeobj->setMsgId($msgId);

        $unsubscribe_topics = array();
        # no need to check if topic is subscribed before unsubscribing
        foreach ($this->topicsToUnsubscribe as $tn => $topic_filter) {
            $unsubscribeobj->addTopic($topic_filter);
            unset($this->topicsToUnsubscribe[$tn]);
            $unsubscribe_topics[] = $topic_filter;
        }

        $unsubscribe_bytes_written = $this->messageWrite($unsubscribeobj);

        Debug::log(Debug::DEBUG, 'unsubscribe(): bytes written=' . $unsubscribe_bytes_written);

        return array($msgId, $unsubscribe_topics);
    }

    /**
     * @var array
     */
    protected $subscribe_awaits = array();
    /**
     * @var array
     */
    protected $unsubscribe_awaits = array();

    /**
     * Message Handler
     *
     * @return bool
     * @throws NetworkError
     * @throws MqttException
     */
    public function handle_message()
    {
        $selected = $this->socket->select($this->keepalive / 2);

        if ($selected === false) {
            # Error
            throw new NetworkError('Connection lost???');
        } else if ($selected) {
            return $this->handleIncoming();
        } else {
            # no incoming packet
            return 0;
        }
    }

    /**
     *
     * @return bool
     * @throws MqttException
     * @throws NetworkError
     */
    protected function handleIncoming()
    {
        $messageObject = $this->messageRead();
        if (!$messageObject) {
            return false;
        }

        switch ($messageObject->getMessageType()) {
            case Message::PINGRESP:
                array_shift($this->ping_queue);
                Debug::log(Debug::INFO, 'loop(): received PINGRESP');

                $this->lastPingTime = time();

                $this->callHandler('pingresp', array($this, $messageObject));

                break;

            # Process PUBLISH
            # in: Client <- Server, Step 1
            case Message::PUBLISH:
                /**
                 * @var Message\PUBLISH $messageObject
                 */

                Debug::log(Debug::INFO, 'loop(): received PUBLISH');

                $qos = $messageObject->getQoS();

                $msgId = $messageObject->getMsgId();

                if ($qos == 0) {
                    Debug::log(Debug::DEBUG, 'loop(): PUBLISH QoS=0 PASS');
                    # Do nothing
                } else if ($qos == 1) {
                    # PUBACK
                    $puback_bytes_written = $this->simpleCommand(Message::PUBACK, $msgId);
                    Debug::log(Debug::DEBUG, 'loop(): PUBLISH QoS=1 PUBACK written=' . $puback_bytes_written);

                } else if ($qos == 2) {

                    # PUBREC
                    $pubrec_bytes_written = $this->simpleCommand(Message::PUBREC, $msgId);
                    Debug::log(Debug::DEBUG, 'loop(): PUBLISH QoS=2 PUBREC written=' . $pubrec_bytes_written);

                    $this->cmdStore->addWait(
                        Message::PUBREL,
                        $msgId,
                        array(
                            'msgid' => $msgId,
                            'retry_after' => time() + $this->retryTimeout,
                        )
                    );

                } else {
                    # wrong packet
                    Debug::log(Debug::WARN, 'loop(): PUBLISH Invalid QoS');
                }

                # call handler
                $this->callHandler('publish', array($this, $messageObject));

                break;

            # Process PUBACK
            # in: Client -> Server, QoS = 1, Step 2
            case Message::PUBACK:

                /**
                 * @var Message\PUBACK $messageObject
                 */

                # Message has been published (QoS 1)
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received PUBACK msgid=' . $msgId);
                # Verify Packet Identifier
                $this->callHandler('puback', array($this, $messageObject));

                $this->cmdStore->deleteWait(Message::PUBACK, $msgId);
                break;

            # Process PUBREC, send PUBREL
            # in: Client -> Server, QoS = 2, Step 2
            case Message::PUBREC:
                /**
                 * @var Message\PUBREC $messageObject
                 */
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received PUBREC msgid=' . $msgId);

                $this->cmdStore->deleteWait(Message::PUBREC, $msgId);

                # PUBREL
                Debug::log(Debug::INFO, 'loop(): send PUBREL msgid=' . $msgId);
                $pubrelBytesWritten = $this->simpleCommand(Message::PUBREL, $msgId);

                $this->cmdStore->addWait(
                    Message::PUBCOMP,
                    $msgId,
                    array(
                        'msgid' => $msgId,
                        'retry_after' => time() + $this->retryTimeout,
                    )
                );

                Debug::log(Debug::DEBUG, 'loop(): PUBREL QoS=2 PUBREL written=' . $pubrelBytesWritten);

                $this->callHandler('pubrec', array($this, $messageObject));
                break;


            # Process PUBREL
            # in: Client <- Server, QoS = 2, Step 3
            case Message::PUBREL:
                /**
                 * @var Message\PUBREL $messageObject
                 */
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received PUBREL msgid=' . $msgId);

                $this->cmdStore->deleteWait(Message::PUBREL, $msgId);

                # PUBCOMP
                Debug::log(Debug::INFO, 'loop(): send PUBCOMP msgid=' . $msgId);
                $pubcomp_bytes_written = $this->simpleCommand(Message::PUBCOMP, $msgId);

                Debug::log(Debug::DEBUG, 'loop(): PUBREL QoS=2 PUBCOMP written=' . $pubcomp_bytes_written);

                $this->callHandler('pubrel', array($this, $messageObject));
                break;

            # Process PUBCOMP
            # in: Client -> Server, QoS = 2, Step 4
            case Message::PUBCOMP:
                # Message has been published (QoS 2)
                /**
                 * @var Message\PUBCOMP $messageObject
                 */
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received PUBCOMP msgid=' . $msgId);

                $this->cmdStore->deleteWait(Message::PUBCOMP, $msgId);

                $this->callHandler('pubcomp', array($this, $messageObject));
                break;

            # Process SUBACK
            case Message::SUBACK:
                /**
                 * @var Message\SUBACK $messageObject
                 */
                $returnCodes = $messageObject->getReturnCodes();
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received SUBACK msgid=' . $msgId);

                if (!isset($this->subscribe_awaits[$msgId])) {
                    Debug::log(Debug::WARN, 'loop(): SUBACK Message identifier not found: ' . $msgId);
                } else {
                    if (count($this->subscribe_awaits[$msgId]) != count($returnCodes)) {
                        Debug::log(Debug::WARN, 'loop(): SUBACK returned qos list doesn\'t match SUBSCRIBE');
                    } else {
                        # save max_qos list from suback
                        foreach ($returnCodes as $k => $tqos) {
                            if ($returnCodes != 0x80) {
                                $this->topics[$this->subscribe_awaits[$msgId][$k][0]] = $tqos;
                            } else {
                                Debug::log(
                                    Debug::WARN,
                                    "loop(): Failed to subscribe '{$this->subscribe_awaits[$msgId][$k][0]}'. Request QoS={$this->subscribe_awaits[$msgId][$k][1]}"
                                );
                            }
                        }
                    }
                }

                $this->callHandler('suback', array($this, $messageObject));
                break;

            # Process UNSUBACK
            case Message::UNSUBACK:
                /**
                 * @var Message\UNSUBACK $messageObject
                 */
                $msgId = $messageObject->getMsgId();
                Debug::log(Debug::INFO, 'loop(): received UNSUBACK msgid=' . $msgId);

                if (!isset($this->unsubscribe_awaits[$msgId])) {
                    Debug::log(Debug::WARN, 'loop(): UNSUBACK Message identifier not found ' . $msgId);
                } else {
                    foreach ($this->unsubscribe_awaits[$msgId] as $topic) {
                        Debug::log(Debug::WARN, "loop(): Unsubscribe topic='{$topic}'");
                        unset($this->topics[$topic]);
                    }
                }

                $this->callHandler('unsuback', array($this, $messageObject));
                break;

            default:
                return false;
        }

        return true;
    }

    /**
     * Handle Publish Retrying
     *
     * @param int $msgId
     * @throws MqttException
     */
    protected function handle_publish($msgId = 0)
    {
        if ($msgId) {
            $time = time();

            # QoS 1
            if (!$this->cmdStore->isEmpty(Message::PUBACK, $msgId)) {
                # resend PUBLISH with dup=1

                $wait = $this->cmdStore->getWait(Message::PUBACK, $msgId);

                if (empty($wait['retry_after']) || $wait['retry_after'] < $time) {
                    $rt = $wait['retry'];

                    $this->doPublish(
                        $rt['topic'],
                        $rt['message'],
                        $qos = 1,
                        $rt['retain'],
                        $msgid,
                        1
                    );
                }
            }

            # QoS 2
            if (!$this->cmdStore->isEmpty(Message::PUBREC, $msgid)) {
                # resend PUBLISH with dup=1

                $wait = $this->cmdStore->getWait(Message::PUBREC, $msgid);
                if (empty($wait['retry_after']) || $wait['retry_after'] < $time) {
                    $rt = $wait['retry'];

                    $this->doPublish(
                        $rt['topic'],
                        $rt['message'],
                        $qos = 2,
                        $rt['retain'],
                        $msgid,
                        1
                    );
                }
            }

            # ??? 干掉?
            # 服务端在下发PUBLISH之后,客户端返回PUBREC,如果长时间客户端不发PUBREL,客户端是否需要重发PUBREC
            if (!$this->cmdStore->isEmpty(Message::PUBREL, $msgid)) {
                Debug::log(Debug::DEBUG, 'handle_publish(): read PUBREL msgid=' . $msgid);

                $wait = $this->cmdStore->getWait(Message::PUBREC, $msgid);
                if (empty($wait['retry_after']) || $wait['retry_after'] < $time) {
                    # resend PUBREC
                    Debug::log(Debug::INFO, 'Resend PUBREC msgid=' . $msgid);
                    $this->simpleCommand(Message::PUBREC, $msgid);
                }
            }

            if (!$this->cmdStore->isEmpty(Message::PUBCOMP, $msgid)) {
                Debug::log(Debug::DEBUG, 'handle_publish(): read PUBCOMP msgid=' . $msgid);

                $wait = $this->cmdStore->getWait(Message::PUBCOMP, $msgid);
                if (empty($wait['retry_after']) || $wait['retry_after'] < $time) {
                    # resend PUBREL
                    Debug::log(Debug::INFO, 'Resend PUBREL msgid=' . $msgid);
                    $this->simpleCommand(Message::PUBREL, $msgid);
                }
            }

        } else {

            $scan_items = array(
                Message::PUBACK,
                Message::PUBREC,
                Message::PUBREL,
                Message::PUBCOMP,
            );

            foreach ($scan_items as $s) {
                if (!$this->cmdStore->isEmpty($s)) {
                    $waits = $this->cmdStore->getWaits($s);

                    foreach ($waits as $msgid => $detail) {
                        $this->handle_publish($msgid);
                    }
                }
            }
        }
    }

    /**
     * Main Loop
     * @throws \Exception
     */
    protected function _loop($first = false)
    {
        Debug::log(Debug::DEBUG, 'loop()');
        while (!$first) {
            # Subscribe topics
            if (!empty($this->topicsToSubscribe)) {
                list($lastSubscribeMsgId, $lastSubscribeTopics) = $this->doSubscribe();
                $this->subscribe_awaits[$last_subscribe_msgid] = $lastSubscribeTopics;
            }
            # Unsubscribe topics
            if (!empty($this->topicsToUnsubscribe)) {
                list($lastUnsubscribeMsgId, $lastUnsubscribeTopics) = $this->doUnsubscribe();
                $this->unsubscribe_awaits[$lastUnsubscribeMsgId] = $lastUnsubscribeTopics;
            }

            try {
                # It is the responsibility of the Client to ensure that the interval between Control Packets
                # being sent does not exceed the Keep Alive value. In the absence of sending any other Control
                # Packets, the Client MUST send a PINGREQ Packet [MQTT-3.1.2-23].
                $this->keepalive();

                $this->handle_message();

            } catch (NetworkError $e) {
                Debug::log(Debug::INFO, 'loop(): Connection lost.');
                $this->reconnect();
                $this->subscribe($this->topics);
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    protected $lastPingTime = 0;

    /**
     * Keep Alive
     *
     * If the Keep Alive value is non-zero and the Server does not receive a Control Packet from the Client
     * within one and a half times the Keep Alive time period, it MUST disconnect the Network Connection to
     * the Client as if the network had failed [MQTT-3.1.2-24].
     *
     * @return bool
     * @throws MqttException
     * @throws NetworkError
     */
    public function keepalive()
    {
        Debug::log(Debug::DEBUG, 'keepalive()');

        $currentTime = time();

        if (empty($this->lastPingTime)) {
            if ($this->connectedTime) {
                $this->lastPingTime = $this->connectedTime;
            } else {
                $this->lastPingTime = $currentTime;
            }
        }

        if ($currentTime - $this->lastPingTime >= $this->keepalive / 2) {
            Debug::log(Debug::DEBUG, "keepalive(): currentTime={$currentTime}, lastPingTime={$this->lastPingTime}, keepalive={$this->keepalive}");
            $this->ping();
        }

        return true;
    }

    protected $ping_queue = array();

    /**
     * Send PINGREQ
     *
     * @return bool
     * @throws MqttException
     * @throws NetworkError
     */
    public function ping()
    {
        Debug::log(Debug::INFO, 'ping()');
        # parse error?
        $ret = $this->simpleCommand(Message::PINGREQ);
        if (!$ret) {
            throw new NetworkError();
        }

        $this->ping_queue[] = time();

        return count($this->ping_queue);
    }

    /**
     * Send Simple Commands
     *
     *
     * @param int $type
     * @param int $msgid
     * @return int           bytes written
     * @throws MqttException
     */
    protected function simpleCommand($type, $msgid = 0)
    {
        $msgobj = $this->getMessageObject($type);

        if ($msgid) {
            $msgobj->setMsgId($msgid);
        }

        return $this->messageWrite($msgobj);
    }

    /**
     * Write Message Object
     *
     * @param Message\Base $object
     * @param int          & $length
     * @return int
     * @throws MqttException
     */
    protected function messageWrite(Base $object, &$length = 0)
    {
        Debug::log(Debug::DEBUG, 'Message write: messageType=' . Message::$name[$object->getMessageType()]);
        $length = 0;
        $message = $object->build($length);
        $bytes_written = $this->socket->write($message, $length);
        return $bytes_written;
    }

    /**
     * EOF counter
     *
     * @var int
     */
    protected $count_eof = 0;

    /*
     * Maximum EOF
     *
     * @var int
     */
    protected $max_eof = 10;

    /**
     * Read Message And Create Message Object
     *
     * @return Message\Base
     * @throws MqttException
     * @throws NetworkError
     */
    protected function messageRead()
    {
        if ($this->socket->eof()) {
            if (++$this->count_eof > 5) {
                usleep(pow(2, $this->count_eof));
            }

            Debug::log(Debug::NOTICE, 'messageRead(): EOF ' . $this->count_eof);

            if ($this->count_eof > $this->max_eof) {
                throw new NetworkError();
            }

            return false;
        }
        # Reset EOF counter
        $this->count_eof = 0;

        # read 2 bytes
        $readFhBytes = 2;
        $read_more_length_bytes = 3;

        $readBytes = 0;
        $read_message = $this->socket->read($readFhBytes);
        if (empty($read_message)) {
            throw new MqttException('WTFFFFFF!!!! ');
        }
        $readBytes += $readFhBytes;

        $cmd = Utility::parseCommand(ord($read_message[0]));

        $messageType = $cmd['message_type'];
        $flags = $cmd['flags'];

        Debug::log(Debug::DEBUG, "messageRead(): messageType=" . Message::$name[$messageType] . ", flags={$flags}");

        if (ord($read_message[1]) > 0x7f) {
            # read 3 more bytes
            $read_message .= $this->socket->read($read_more_length_bytes);
            $readBytes += $read_more_length_bytes;
        }

        $pos = 1;
        $remainingLength = Utility::decodeLength($read_message, $pos);

        $toRead = 0;
        if ($remainingLength) {
            $toRead = $remainingLength - ($readBytes - $pos);
        }

        Debug::log(Debug::DEBUG, 'messageRead(): remaining length=' . $remainingLength . ', data to read=' . $toRead);
        if ($toRead) {
            $read_message .= $this->socket->read($toRead);
        }

        Debug::log(Debug::DEBUG, 'messageRead(): Dump', $read_message);

        $messageObject = $this->getMessageObject($messageType);
        $messageObject->decode($read_message, $remainingLength);

        return $messageObject;
    }
}
