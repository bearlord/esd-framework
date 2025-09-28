<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Hex\ReasonCode;
use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

class DisConnect extends AbstractMessage
{
    protected $code = ReasonCode::NORMAL_DISCONNECTION;

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
     * @param bool $getArray
     * @return array|string
     * @throws \Throwable
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::DISCONNECT,
        ];

        if ($this->isMQTT5()) {
            $buffer['code'] = $this->getCode();
            $buffer['properties'] = $this->getProperties();
        }

        if ($getArray) {
            return $buffer;
        }

        if ($this->isMQTT5()) {
            return ProtocolV5::pack($buffer);
        }

        return ProtocolV3::pack($buffer);
    }
}
