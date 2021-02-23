<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message PUBCOMP
 * Client <-> Server
 */
class PUBCOMP extends Base
{
    protected $messageType = Message::PUBCOMP;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

}