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
 * Class SubAck
 * @package ESD\Plugins\MQTT\Message
 */
class SubAck extends AbstractMessage
{
    /**
     * @var int
     */
    protected $messageId = 0;

    protected $codes = [];

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @param int $messageId
     * @return $this
     */
    public function setMessageId(int $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * @return array
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * @param array $codes
     * @return $this
     */
    public function setCodes(array $codes): self
    {
        $this->codes = $codes;

        return $this;
    }

    /**
     * @param bool $getArray
     * @return array|mixed|string
     * @throws \Throwable
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::SUBACK,
            'message_id' => $this->getMessageId(),
            'codes' => $this->getCodes(),
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
