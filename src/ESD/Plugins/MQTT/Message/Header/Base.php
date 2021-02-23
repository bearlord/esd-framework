<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;

use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\MqttException;
use ESD\Plugins\MQTT\Utility;


/**
 * Base class for Headers
 *
 * Fixed Header
 *
 * First two or more bytes in Control Packets
 * 4-bit MQTT Control Packet type, 4-bit Flags, 1+ bytes remaining length
 */
class Base
{
    /**
     * Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x0;

    /**
     * Remaining Length
     *
     * @var int
     */
    protected $remainingLength = 0;

    /**
     * Encoded Remaining Length
     *
     * @var string
     */
    protected $remainingLengthBytes = '';

    /**
     * Is Packet Identifier A MUST
     *
     * @var bool
     */
    protected $requireMsgId = false;

    /**
     * Packet Identifier
     *
     * @var int
     */
    public $msgId = 0;

    /**
     *
     * @var \ESD\Plugins\MQTT\Message\Base
     */
    protected $message;

    public function __construct(\ESD\Plugins\MQTT\Message\Base $message)
    {
        $this->message = $message;
    }

    /**
     * Decode Packet Header and returns payload position.
     *
     * @param string & $packetData
     * @param int $remainingLength
     * @param int    & $payloadPos
     * @throws \ESD\Plugins\MQTT\MqttException
     */
    final public function decode(&$packetData, $remainingLength, &$payloadPos)
    {
        $cmd = Utility::ParseCommand(ord($packetData[0]));
        $messageType = $cmd['message_type'];
        if ($this->message->getMessageType() != $messageType) {
            throw new MqttException('Unexpected Control Packet Type');
        }

        $flags = $cmd['flags'];
        $this->setFlags($flags);

        $pos = 1;
        $rl_len = strlen(($this->remainingLengthBytes = Utility::EncodeLength($remainingLength)));
        $pos += $rl_len;

        $this->remainingLength = $remainingLength;
        $this->decodeVariableHeader($packetData, $pos);
        $payloadPos = $pos;
    }

    /**
     * Set Flags
     *
     * @param int $flags
     * @return bool
     * @throws MqttException
     */
    protected function setFlags($flags)
    {
        if ($flags != $this->reservedFlags) {
            throw new MqttException('Flags mismatch.');
        }

        return true;
    }

    /**
     * Decode Variable Header
     *
     * @param string & $packetData
     * @param int    & $pos
     * @return bool
     */
    protected function decodeVariableHeader(&$packetData, &$pos)
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
        return $this->message->getMessageType();
    }

    /**
     * Set Remaining Length
     *
     * @param int $length
     */
    public function setRemainingLength($length)
    {
        $this->remainingLength = $length;
        $this->remainingLengthBytes = Utility::EncodeLength($this->remainingLength);
    }

    /**
     * Set Payload Length
     *
     * @param int $length
     * @throws MqttException
     */
    public function setPayloadLength($length)
    {
        $this->setRemainingLength($length + strlen($this->buildVariableHeader()));
    }

    /**
     * Get Remaining Length Field
     *
     * @return int
     */
    public function getRemainingLength()
    {
        return $this->remainingLength;
    }

    /**
     * Get Full Header Length
     *
     * @return int
     */
    public function getFullLength()
    {
        $cmdLength = 1;

        $rlLength = strlen($this->remainingLengthBytes);

        return $cmdLength + $rlLength + $this->remainingLength;
    }

    /**
     * Set Packet Identifier
     *
     * @param int $msgId
     * @throws MqttException
     */
    public function setMsgId($msgId)
    {
        Utility::CheckPacketIdentifier($msgId);

        $this->msgid = $msgId;
    }

    /**
     * Get Packet Identifier
     *
     * @return int
     */
    public function getMsgId()
    {
        return $this->msgid;
    }

    /**
     * Default Variable Header
     *
     * @return string
     * @throws MqttException
     */
    protected function buildVariableHeader()
    {
        $buffer = '';
        # Variable Header
        # Packet Identifier
        if ($this->requireMsgId) {
            $buffer .= $this->packPacketIdentifer();
        }

        return $buffer;
    }

    final protected function packPacketIdentifer()
    {
        if (!$this->msgid) {
            throw new MqttException('Invalid Packet Identifier');
        }

        Debug::Log(Debug::DEBUG, 'msgid=' . $this->msgid);
        return pack('n', $this->msgid);
    }

    final protected function decodePacketIdentifier(&$packetData, &$pos)
    {
        $msgId = Utility::ExtractUShort($packetData, $pos);
        $this->setMsgId($msgId);

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
        # Fixed Header
        # Control Packet Type
        $cmd = $this->getMessageType() << 4;

        $cmd |= ($this->reservedFlags & 0x0F);

        $header = chr($cmd) . $this->remainingLengthBytes;

        $header .= $this->buildVariableHeader();

        return $header;
    }
}