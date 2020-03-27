<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;

/**
 * Message PINGREQ
 * Client -> Server
 *
 * 3.12 PINGREQ – PING request
 */
class PINGREQ extends Base
{
    protected $message_type = Message::PINGREQ;
    protected $protocol_type = self::FIXED_ONLY;
}

# EOF