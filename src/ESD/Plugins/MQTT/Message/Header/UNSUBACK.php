<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Message\Header;
use ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Utility;


/**
 * Fixed Header definition for UNSUBACK
 */
class UNSUBACK extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reserved_flags = 0x00;

    /**
     * UNSUBSCRIBE requires Packet Identifier
     *
     * @var bool
     */
    protected $require_msgid = true;

    /**
     * Decode Variable Header
     *
     * Packet Identifier
     *
     * @param string & $packet_data
     * @param int    & $pos
     * @return bool
     */
    protected function decodeVariableHeader(& $packet_data, & $pos)
    {
        return $this->decodePacketIdentifier($packet_data, $pos);
    }
}

# EOF