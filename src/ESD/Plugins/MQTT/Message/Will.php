<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Message;

use ESD\Plugins\MQTT\Protocol\ProtocolInterface;

class Will extends AbstractMessage
{
    /**
     * @var string
     */
    protected $topic = "";

    /**
     * @var int
     */
    protected $qos = ProtocolInterface::MQTT_QOS_0;

    /**
     * @var int
     */
    protected $retain = ProtocolInterface::MQTT_RETAIN_0;

    /**
     * @var string
     */
    protected $message = "";

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
     * @param bool $getArray
     * @return array
     */
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'topic' => $this->getTopic(),
            'qos' => $this->getQos(),
            'retain' => $this->getRetain(),
            'message' => $this->getMessage(),
        ];

        if ($this->isMQTT5()) {
            $buffer['properties'] = $this->getProperties();
        }

        if ($getArray) {
            return $buffer;
        }

        // The will message can only be an array
        return [];
    }
}
