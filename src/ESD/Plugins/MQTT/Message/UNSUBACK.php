<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message UNSUBACK
 * Client <- Server
 *
 * 3.11 UNSUBACK – Unsubscribe acknowledgement
 */
class UNSUBACK extends Base
{
    protected $messageType = Message::UNSUBACK;
    protected $protocolType = self::FIXED_ONLY;
    protected $readBytes = 4;

}