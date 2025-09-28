<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Hex\ReasonCode;
use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

class Auth extends AbstractMessage
{
    protected $code = ReasonCode::SUCCESS;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * AUTH type is only available in MQTT5
     *
     * @param bool $getArray
     * @return array|string
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::AUTH,
            'code' => $this->getCode(),
            'properties' => $this->getProperties(),
        ];

        if ($getArray) {
            return $buffer;
        }

        return ProtocolV5::pack($buffer);
    }
}
