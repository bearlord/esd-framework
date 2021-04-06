<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message PUBREC
 * Client <-> Server
 *
 * 3.5 PUBREC – Publish received (QoS 2 publish received, part 1)
 */
class PUBREC extends Base
{
    protected $messageType = Message::PUBREC;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

}