<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message\Header;

use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Utility;


/**
 * Fixed Header definition for PUBCOMP
 */
class PUBCOMP extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reservedFlags = 0x00;

    /**
     * PUBCOMP requires have Packet Identifier
     *
     * @var bool
     */
    protected $requireMsgId = true;

    /**
     * Decode Variable Header
     *
     * Packet Identifier
     *
     * @param string & $packetData
     * @param int    & $pos
     * @return bool
     */
    protected function decodeVariableHeader(&$packetData, &$pos)
    {
        return $this->decodePacketIdentifier($packetData, $pos);
    }

}