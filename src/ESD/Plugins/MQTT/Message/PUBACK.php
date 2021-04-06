<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
    protected $messageType = Message::PUBACK;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

}