<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;
use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Utility;
use ESD\Plugins\MQTT\MqttException;


/**
 * Fixed Header definition for PUBLISH
 *
 * @property \ESD\Plugins\MQTT\Message\PUBLISH $message
 */
class PUBLISH extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x00;

    /**
     * Required when QoS > 0
     *
     * @var bool
     */
    protected $requireMsgId = false;

    protected $dup    = 0; # 1-bit
    protected $qos    = 0; # 2-bit
    protected $retain = 0; # 1-bit

    /**
     * Set DUP
     *
     * @param bool|int $dup
     */
    public function setDup($dup)
    {
        $this->dup = $dup ? 1 : 0;
    }

    /**
     * Get DUP
     *
     * @return int
     */
    public function getDup()
    {
        return $this->dup;
    }

    /**
     * Set QoS
     *
     * @param int $qos 0,1,2
     * @throws MqttException
     */
    public function setQos($qos)
    {
        Utility::checkQoS($qos);
        $this->qos = (int) $qos;

        if ($this->qos > 0) {
            /*
              SUBSCRIBE, UNSUBSCRIBE, and PUBLISH (in cases where QoS > 0) Control Packets MUST contain a
              non-zero 16-bit Packet Identifier [MQTT-2.3.1-1].
             */
            $this->requireMsgId = true;
        } else {
            /*
              A PUBLISH Packet MUST NOT contain a Packet Identifier if its QoS value is set to 0 [MQTT-2.3.1-5].
             */
            $this->requireMsgId = false;
        }
    }

    /**
     * Get QoS
     *
     * @return int
     */
    public function getQos()
    {
        return $this->qos;
    }

    /**
     * Set RETAIN
     *
     * @param bool|int $retain
     */
    public function setRetain($retain)
    {
        $this->retain = $retain ? 1 : 0;
    }

    /**
     * Get RETAIN
     *
     * @return int
     */
    public function getRetain()
    {
        return $this->retain;
    }

    /**
     * Set Flags
     *
     * @param int $flags
     * @return bool
     */
    public function setFlags($flags)
    {
        $flags = Utility::parseFlags($flags);

        $this->setDup($flags['dup']);
        $this->setQos($flags['qos']);
        $this->setRetain($flags['retain']);
        return true;
    }

    /**
     * Set Packet Identifier
     *
     * @param int $msgId
     * @throws MqttException
     */
    public function setMsgId($msgId)
    {
        /*
         A PUBLISH Packet MUST NOT contain a Packet Identifier if its QoS value is set to 0 [MQTT-2.3.1-5].
         */
        if ($this->qos) {
            parent::setMsgId($msgId);
        } else if ($msgId) {
            throw new MqttException('MsgId MUST NOT be set if QoS is set to 0.');
        }
    }

    /**
     * PUBLISH Variable Header
     *
     * Topic Name, Packet Identifier
     *
     * @return string
     * @throws MqttException
     */
    protected function buildVariableHeader()
    {
        $header = '';

        $topic = $this->message->getTopic();
        # Topic
        $header .= Utility::packStringWithLength($topic);
        Debug::log(Debug::DEBUG, 'Message PUBLISH: topic='.$topic);

        Debug::log(Debug::DEBUG, 'Message PUBLISH: QoS='.$this->getQos());
        Debug::log(Debug::DEBUG, 'Message PUBLISH: DUP='.$this->getDup());
        Debug::log(Debug::DEBUG, 'Message PUBLISH: RETAIN='.$this->getRetain());

        # Message ID if QoS > 0
        if ($this->getQos()) {
            if (!$this->msgid) {
                throw new MqttException('MsgId MUST be set if QoS is not 0.');
            }

            $header .= $this->packPacketIdentifer();
        }

        return $header;
    }

    /**
     * Decode Variable Header
     * Topic, Packet Identifier
     *
     * @param string & $packetData
     * @param int    & $pos
     * @return bool
     */
    protected function decodeVariableHeader(& $packetData, & $pos)
    {
        $topic = Utility::unpackStringWithLength($packetData, $pos);
        $this->message->setTopic($topic);

        if ($this->getQos() > 0) {
            # Decode Packet Identifier if QoS > 0
            $this->decodePacketIdentifier($packetData, $pos);
        }

        return true;
    }

    /**
     * Build fixed Header packet
     *
     * @return string
     * @throws MqttException
     */
    public function build()
    {
        $flags = 0;

        if (!$this->getQos()) {
            if ($this->getDup()) {
                /*
                 In the QoS 0 delivery protocol, the Sender MUST send a PUBLISH packet with QoS=0, DUP=0 [MQTT-4.3.1-1].
                 */
                throw new MqttException('DUP MUST BE 0 if QoS is 0');
            }
        }

        /**
         * Flags for fixed Header
         *
         * This 4-bit number was defined as DUP,QoS 1,QoS 0,RETAIN in MQTT 3.1,
         * In 3.1.1, only PUBLISH has those names, for PUBREL, SUBSCRIBE, UNSUBSCRIBE: 0010; and others, 0000
         *
         * The definition DUP, QoS, RETAIN works in 3.1.1, and literally,
         * it means the same for PUBREL, SUBSCRIBE and UNSCRIBE.
         */
        $flags |= ($this->dup << 3);
        $flags |= ($this->qos << 1);
        $flags |= $this->retain;

        $this->reservedFlags = $flags;

        return parent::build();
    }
}