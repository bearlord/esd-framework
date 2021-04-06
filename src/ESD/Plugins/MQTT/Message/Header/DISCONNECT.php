<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;
use ESD\Plugins\MQTT\MqttException;


/**
 * Fixed Header definition for DISCONNECT
 */
class DISCONNECT extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x00;

    /**
     * DISCONNECT does not have Packet Identifier
     *
     * @var bool
     */
    protected $requireMsgId = false;

    /**
     * Decode Variable Header
     *
     * @param string & $packetData
     * @param int    & $pos
     * @return bool
     * @throws MqttException
     */
    protected function decodeVariableHeader(& $packetData, & $pos)
    {
        return;
    }
}