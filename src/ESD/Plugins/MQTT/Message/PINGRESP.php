<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Debug;
use ESD\Plugins\MQTT\Utility;
use ESD\Plugins\MQTT\Message;

/**
 * Message PINGRESP
 * Client <- Server
 *
 * 3.13 PINGRESP – PING response
 */
class PINGRESP extends Base
{
    protected $message_type = Message::PINGRESP;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 2;
}

# EOF