<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp;

/**
 * Class Builder
 * @package ESD\Plugins\Amqp
 */
class Builder
{
    use GetAmqp;

    /**
     * @var Handle;
     */
    protected $handle;

    const AMQP_X_DELAY = 'x-delay';
    const AMQP_X_DELAYED_MESSAGE = 'x-delayed-message';
    const AMQP_X_DELAYED_TYPE = 'x-delayed-type';

    /**
     * The property tells whether the setupBroker method was called or not.
     * Having it we can do broker setup only once per process.
     *
     * @var bool
     */
    protected $setupBrokerDone = false;

    /**
     * The queue used to consume messages from.
     *
     * @var string
     */
    public $queueName = 'esd-queue';

    /**
     * The exchange used to publish messages to.
     *
     * @var string
     */
    public $exchangeName = 'esd-exchange';

    /**
     * The routing key
     *
     * @var string
     */
    public $routingKey = 'esd-routing-key';

    /**
     * This property should be an integer indicating the maximum priority the queue should support. Default is 10.
     *
     * @var int
     */
    public $maxPriority = 10;

    public function __construct($name = 'default')
    {
        $this->handle = $this->amqp($name);
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName(string $queueName): void
    {
        $this->queueName = $queueName;
    }

    /**
     * @return string
     */
    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }

    /**
     * @param string $exchangeName
     */
    public function setExchangeName(string $exchangeName): void
    {
        $this->exchangeName = $exchangeName;
    }

    /**
     * @return string
     */
    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    /**
     * @param string $routingKey
     */
    public function setRoutingKey(string $routingKey): void
    {
        $this->routingKey = $routingKey;
    }

    /**
     * @return int
     */
    public function getMaxPriority(): int
    {
        return $this->maxPriority;
    }

    /**
     * @param int $maxPriority
     */
    public function setMaxPriority(int $maxPriority): void
    {
        $this->maxPriority = $maxPriority;
    }

    /**
     * @param $value
     * @return false|string
     */
    public function encode($value)
    {
        if (is_string($value)) {
            return $value;
        }
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $value
     * @return mixed|string
     */
    public function decode($value)
    {
        if (is_string($value)) {
            return $value;
        }
        return json_decode($value, true);
    }
}