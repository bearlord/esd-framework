<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp\Message;

use ESD\Yii\Helpers\Json;

/**
 * Class ProducerMessage
 * @package ESD\Plugins\Amqp\Message
 */
class ProducerMessage extends Message
{
    /**
     * @var string
     */
    protected $queue;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var array
     */
    protected $properties = [
        'content_type' => 'text/plain',
        'delivery_mode' => self::DELIVERY_MODE_PERSISTENT
    ];

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @param string $queue
     */
    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setPayload($data): self
    {
        $this->payload = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function serialize(): string
    {
        return json_encode($this->payload, JSON_UNESCAPED_UNICODE);
    }
}