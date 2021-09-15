<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Hex\ReasonCode;
use ESD\Plugins\MQTT\Protocol\ProtocolInterface;
use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

/**
 * Class AbstractMessage
 * @package ESD\Plugins\MQTT\Message
 */
class ConnAck extends AbstractMessage
{
    /**
     * @var int
     */
    protected $code = ReasonCode::SUCCESS;

    /**
     * @var int
     */
    protected $sessionPresent = ProtocolInterface::MQTT_SESSION_PRESENT_0;

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
     * @return int
     */
    public function getSessionPresent(): int
    {
        return $this->sessionPresent;
    }

    /**
     * @param int $sessionPresent
     * @return $this
     */
    public function setSessionPresent(int $sessionPresent): self
    {
        $this->sessionPresent = $sessionPresent;

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
            'type' => Types::CONNACK,
            'code' => $this->getCode(),
            'session_present' => $this->getSessionPresent(),
        ];

        if ($this->isMQTT5()) {
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
