<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message PUBACK
 * Client <-> Server
 *
 * 3.4 PUBACK – Publish acknowledgement
 */
class PUBACK extends Base
{
    protected $message_type = Message::PUBACK;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;

}

# EOF