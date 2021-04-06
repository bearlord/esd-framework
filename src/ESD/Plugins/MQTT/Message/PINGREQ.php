<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
    protected $messageType = Message::PINGREQ;
    protected $protocolType = self::FIXED_ONLY;
}