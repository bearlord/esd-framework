<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;
use ESD\Plugins\MQTT\MqttException;

/**
 * Fixed Header definition for UNSUBSCRIBE
 */
class UNSUBSCRIBE extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x02;

    /**
     * UNSUBSCRIBE requires Packet Identifier
     *
     * @var bool
     */
    protected $requireMsgId = true;

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
        return $this->decodePacketIdentifier($packetData, $pos);
    }
}