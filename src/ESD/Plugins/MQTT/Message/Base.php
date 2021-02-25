<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\MqttException;
use ESD\Plugins\MQTT\IMqtt;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Utility;

/**
 * Base class for MQTT Messages
 */
abstract class Base
{
    /**
     * Message with Fixed Header ONLY
     *
     * CONNECT, CONNACK, PINGREQ, PINGRESP, DISCONNECT
     */
    const FIXED_ONLY = 0x01;

    /**
     * Message with Variable Header
     * Fixed Header + Variable Header
     *
     */
    const WITH_VARIABLE = 0x02;
    /**
     * Message with Payload
     * Fixed Header + Variable Header + Payload
     *
     */
    const WITH_PAYLOAD = 0x03;
    /**
     * Protocol Type
     * Constants FIXED_ONLY, WITH_VARIABLE, WITH_PAYLOAD
     *
     * @var int
     */
    protected $protocolType = self::FIXED_ONLY;

    /**
     * @var IMqtt
     */
    public $mqtt;

    /**
     * Bytes to read
     *
     * @var int
     */
    protected $readBytes = 0;

    /**
     * @var header\Base
     */
    public $header = null;

    /**
     * Control Packet Type
     *
     * @var int
     */
    protected $messageType = 0;

    /**
     * Base constructor.
     * @param IMqtt $mqtt
     */
    public function __construct(IMqtt $mqtt)
    {
        $this->mqtt = $mqtt;

        $headerClass = __NAMESPACE__ . '\\Header\\' . Message::$name[$this->messageType];

        $this->header = new $headerClass($this);
    }

    /**
     * @param $packetData
     * @param $remainingLength
     * @return bool
     * @throws MqttException
     */
    final public function decode($packetData, $remainingLength)
    {
        $payloadPos = 0;

        $this->header->decode($packetData, $remainingLength, $payloadPos);
        return $this->decodePayload($packetData, $payloadPos);
    }

    /**
     * @param $packetData
     * @param $payloadPos
     * @return bool
     */
    protected function decodePayload(&$packetData, &$payloadPos)
    {
        return true;
    }

    /**
     * Get Control Packet Type
     *
     * @return int
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Get Protocol Type
     *
     * @return int
     */
    public function getProtocolType()
    {
        return $this->protocolType;
    }

    /**
     * Set Packet Identifier
     *
     * @param int $msgId
     */
    public function setMsgId($msgId)
    {
        $this->header->setMsgId($msgId);
    }

    /**
     * Get Packet Identifier
     *
     * @return int
     */
    public function getMsgId()
    {
        return $this->header->getMsgId();
    }


    /**
     * Build packet data
     *
     * @param int & $length
     * @return string
     * @throws MqttException
     */
    final public function build(&$length = 0)
    {
        switch ($this->protocolType) {
            case self::FIXED_ONLY:
            case self::WITH_VARIABLE:
            case self::WITH_PAYLOAD:
                $payload = $this->payload();
                break;
            default:
                throw new MqttException('Invalid protocol type');
        }

        $length = strlen($payload);
        $this->header->setPayloadLength($length);

        $length = $this->header->getFullLength();
        Debug::log(Debug::DEBUG, 'Message Build: total length=' . $length);

        return $this->header->build() . $payload;
    }

    protected $payload = '';

    /**
     * Prepare Payload
     * Empty payload by default
     *
     * @return string
     */
    protected function payload()
    {
        return $this->payload;
    }

    /**
     * Process packet with Fixed Header + Message Identifier only
     *
     * @param string $message
     * @return array|bool
     */
    final protected function processReadFixedHeaderWithMsgId($message)
    {
        $packetLength = 4;
        $name = Message::$name[$this->messageType];

        if (!isset($message[$packetLength - 1])) {
            # error
            Debug::log(Debug::DEBUG, "Message {$name}: error on reading");
            return false;
        }

        $packet = unpack('Ccmd/Clength/nmsgid', $message);

        $packet['cmd'] = Utility::unpackCommand($packet['cmd']);

        if ($packet['cmd']['message_type'] != $this->getMessageType()) {
            Debug::log(Debug::DEBUG, "Message {$name}: type mismatch");
            return false;
        } else {
            Debug::log(Debug::DEBUG, "Message {$name}: success");
            return $packet;
        }
    }

    /**
     * @param $messages
     * @return array
     */
    protected function readUTF($messages)
    {
        $arr = [];
        while (strlen($messages) != 0) {
            $length = unpack("n", $messages)[1];
            $message = substr($messages, 2, $length);
            $arr[] = $message;
            $messages = substr($messages, 2 + $length);
        }
        return $arr;
    }
}