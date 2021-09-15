<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Protocol\ProtocolInterface;
use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

/**
 * Class AbstractMessage
 * @package ESD\Plugins\MQTT\Message
 */
class Publish extends AbstractMessage
{
    /**
     * @var string
     */
    protected $topic = "";

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var int
     */
    protected $qos = ProtocolInterface::MQTT_QOS_0;

    /**
     * @var int
     */
    protected $dup = ProtocolInterface::MQTT_DUP_0;

    /**
     * @var int
     */
    protected $retain = ProtocolInterface::MQTT_RETAIN_0;

    /**
     * @var int
     */
    protected $messageId = 0;

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     * @return $this
     */
    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getQos(): int
    {
        return $this->qos;
    }

    /**
     * @param int $qos
     * @return $this
     */
    public function setQos(int $qos): self
    {
        $this->qos = $qos;

        return $this;
    }

    /**
     * @return int
     */
    public function getDup(): int
    {
        return $this->dup;
    }

    /**
     * @param int $dup
     * @return $this
     */
    public function setDup(int $dup): self
    {
        $this->dup = $dup;

        return $this;
    }

    /**
     * @return int
     */
    public function getRetain(): int
    {
        return $this->retain;
    }

    /**
     * @param int $retain
     * @return $this
     */
    public function setRetain(int $retain): self
    {
        $this->retain = $retain;

        return $this;
    }

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
     * @param bool $getArray
     * @return array|mixed|string
     * @throws \Throwable
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::PUBLISH,
            'topic' => $this->getTopic(),
            'message' => $this->getMessage(),
            'dup' => $this->getDup(),
            'qos' => $this->getQos(),
            'retain' => $this->getRetain(),
            'message_id' => $this->getMessageId(),
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
