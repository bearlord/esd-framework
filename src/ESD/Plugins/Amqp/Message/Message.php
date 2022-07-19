<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp\Message;

use ESD\Plugins\Amqp\Builder\ExchangeBuilder;

/**
 * Class Message
 * @package ESD\Plugins\Amqp\Message
 */
abstract class Message implements MessageInterface
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;

    const DELIVERY_MODE_PERSISTENT = 2;

    /**
     * @var string
     */
    protected $poolName = 'default';

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

    /**
     * @return string
     */
    public function getPoolName(): string
    {
        return $this->poolName;
    }

    /**
     * @return ExchangeBuilder
     */
    public function getExchangeBuilder(): ExchangeBuilder
    {
        return (new ExchangeBuilder())->setExchange($this->getExchange())->setType($this->getType());
    }

    public function serialize(): string
    {
        throw new MessageException('You have to overwrite serialize() method.');
    }

    public function unserialize(string $data)
    {
        throw new MessageException('You have to overwrite unserialize() method.');
    }
}