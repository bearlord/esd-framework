<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
    protected $messageType = Message::PINGRESP;
    protected $protocolType = self::FIXED_ONLY;
    protected $readBytes = 2;
}