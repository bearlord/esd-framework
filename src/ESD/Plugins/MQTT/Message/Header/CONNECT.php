<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;

use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\MqttException;
use ESD\Plugins\MQTT\MQTT;
use ESD\Plugins\MQTT\Utility;

/**
 * Fixed Header definition for CONNECT
 *
 * @property \ESD\Plugins\MQTT\Message\CONNECT $message
 */
class CONNECT extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x00;

    /**
     * CONNECT does not have Packet Identifier
     *
     * @var bool
     */
    protected $requireMsgId = false;

    /**
     * Clean Session
     *
     * @var int
     */
    protected $clean = 1;

    /**
     * KeepAlive
     *
     * @var int
     */
    protected $keepalive = 60;

    protected $willFlag;

    protected $willQos;

    protected $willRetain;

    protected $usernameFlag;

    protected $passwordFlag;

    /**
     * @return int
     */
    public function getClean()
    {
        return $this->clean;
    }

    /**
     * @return int
     */
    public function getKeepAlive()
    {
        return $this->keepalive;
    }

    /**
     * @return mixed
     */
    public function getWillFlag()
    {
        return $this->willFlag;
    }

    /**
     * @return mixed
     */
    public function getWillQos()
    {
        return $this->willQos;
    }

    /**
     * @return mixed
     */
    public function getWillRetain()
    {
        return $this->willRetain;
    }

    /**
     * @return mixed
     */
    public function getUserNameFlag()
    {
        return $this->usernameFlag;
    }

    /**
     * @return mixed
     */
    public function getPassWordFlag()
    {
        return $this->passwordFlag;
    }

    /**
     * Clean Session
     *
     * Session is not stored currently.
     *
     * @todo Store Session  MQTT-3.1.2-4, MQTT-3.1.2-5
     * @param int $clean
     */
    public function setClean($clean)
    {
        $this->clean = $clean ? 1 : 0;
    }

    /**
     * Keep Alive
     *
     * @param int $keepalive
     */
    public function setKeepalive($keepalive)
    {
        $this->keepalive = (int)$keepalive;
    }

    /**
     * Build Variable Header
     *
     * @return string
     */
    protected function buildVariableHeader()
    {
        $buffer = "";

        # Protocol Name
        if ($this->message->mqtt->version() == MQTT::VERSION_3_1_1) {
            $buffer .= Utility::packStringWithLength('MQTT');

        } else {
            $buffer .= Utility::packStringWithLength('MQIsdp');
        }
        # End of Protocol Name

        # Protocol Level
        $buffer .= chr($this->message->mqtt->version());

        # Connect Flags
        # Set to 0 by default
        $var = 0;
        # clean session
        if ($this->clean) {
            $var |= 0x02;
        }
        # Will flags
        if ($this->message->will) {
            $var |= $this->message->will->get();
        }

        # User name flag
        if ($this->message->username != NULL) {
            $var |= 0x80;
        }
        # Password flag
        if ($this->message->password != NULL) {
            $var |= 0x40;
        }

        $buffer .= chr($var);
        # End of Connect Flags

        # Keep alive: unsigned short 16bits big endian
        $buffer .= pack('n', $this->keepalive);

        return $buffer;
    }

    /**
     * Decode Variable Header
     *
     * @param string & $packetData
     * @param int & $pos
     * @return bool
     * @throws MqttException
     */
    protected function decodeVariableHeader(& $packetData, & $pos)
    {
        Debug::log(Debug::DEBUG, "CONNECT", $packetData);
        $pos++;
        //Protocol Name
        $length = ord($packetData[$pos]);
        $pos += $length + 1;
        //Protocol Level
        $level = ord($packetData[$pos]);
        $pos++;
        //Connect Flags
        $flags = ord($packetData[$pos]);
        $reserved = $flags & 0x01;
        $this->clean = ($flags & 0x02) >> 1;
        $this->willFlag = ($flags & 0x04) >> 2;
        $this->willQos = ($flags & 0x18) >> 3;
        $this->willRetain = ($flags & 0x20) >> 5;
        $this->usernameFlag = ($flags & 0x80) >> 7;
        $this->passwordFlag = ($flags & 0x40) >> 6;
        $pos++;
        //Keep Alive
        $keep_alive_msb = ord($packetData[$pos]);
        $pos++;
        $keep_alive_lsb = ord($packetData[$pos]);
        $this->keepalive = $keep_alive_msb * 128 + $keep_alive_lsb;
        $pos++;
    }
}