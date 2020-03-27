<?php

/**
 * MQTT Client
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message CONNACK
 * Client <- Server
 *
 * 3.2 CONNACK – Acknowledge connection request
 *
 */
class CONNACK extends Base
{
    protected $message_type = Message::CONNACK;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;

    /**
     * @param $return_code
     */
    public function setReturnCode($return_code)
    {
        $this->header->setReturnCode($return_code);
    }

    public function setSessionPresent($session_present)
    {
        $this->header->setSessionPresent($session_present);
    }
}

# EOF