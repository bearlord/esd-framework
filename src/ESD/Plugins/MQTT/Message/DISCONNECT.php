<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Message;

/**
 * Message DISCONNECT
 * Client -> Server
 *
 * 3.14 DISCONNECT – Disconnect notification
 */
class DISCONNECT extends Base
{
    protected $messageType = Message::DISCONNECT;
    protected $protocolType = self::FIXED_ONLY;
}