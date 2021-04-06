<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
    protected $messageType = Message::CONNACK;
    protected $protocolType = self::WITH_VARIABLE;
    protected $readBytes = 4;

    /**
     * @param $returnCode
     */
    public function setReturnCode($returnCode)
    {
        $this->header->setReturnCode($returnCode);
    }

    public function setSessionPresent($sessionPresent)
    {
        $this->header->setSessionPresent($sessionPresent);
    }
}