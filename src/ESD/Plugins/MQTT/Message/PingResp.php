<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

/**
 * Class PingResp
 * @package ESD\Plugins\MQTT\Message
 */
class PingResp extends AbstractMessage
{
    /**
     * @param bool $getArray
     * @return array|mixed|string
     * @throws \Throwable
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::PINGRESP,
        ];

        if ($getArray) {
            return $buffer;
        }

        if ($this->isMQTT5()) {
            return ProtocolV5::pack($buffer);
        }

        return ProtocolV3::pack($buffer);
    }
}
