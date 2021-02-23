<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;
use ESD\Plugins\MQTT\Message;


/**
 * Message PUBREL
 * Client <-> Server
 *
 * 3.6 PUBREL – Publish release (QoS 2 publish received, part 2)
 */
class PUBREL extends Base
{
    protected $messageType = Message::PUBREL;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

}