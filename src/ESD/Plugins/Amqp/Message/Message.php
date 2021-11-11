<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp\Message;

/**
 * Class Message
 * @package ESD\Plugins\Amqp\Message
 */
abstract class Message
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;

    const DELIVERY_MODE_PERSISTENT = 2;

    /**
     * @var string
     */
    protected $exchange = '';

    /**
     * @var string
     */
    protected $type = Type::TOPIC;

    /**
     * @var array|string
     */
    protected $routingKey = '';

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @param string $exchange
     */
    public function setExchange(string $exchange): void
    {
        $this->exchange = $exchange;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array|string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * @param array|string $routingKey
     */
    public function setRoutingKey($routingKey): void
    {
        $this->routingKey = $routingKey;
    }
}