<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp\Builder;

use ESD\Plugins\Amqp\GetAmqp;
use ESD\Plugins\Amqp\Message\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Class Builder
 * @package ESD\Plugins\Amqp\Builder
 */
class Builder
{
    use GetAmqp;

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


    /**
     * @var bool
     */
    protected $passive = false;

    /**
     * @var bool
     */
    protected $durable = true;

    /**
     * @var bool
     */
    protected $autoDelete = false;

    /**
     * @var bool
     */
    protected $nowait = false;

    /**
     * @var AMQPTable|array
     */
    protected $arguments = [];

    /**
     * @var null|int
     */
    protected $ticket;

    public function isPassive(): bool
    {
        return $this->passive;
    }

    /**
     * @return static
     */
    public function setPassive(bool $passive): self
    {
        $this->passive = $passive;
        return $this;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    /**
     * @return static
     */
    public function setDurable(bool $durable): self
    {
        $this->durable = $durable;
        return $this;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    /**
     * @return static
     */
    public function setAutoDelete(bool $autoDelete): self
    {
        $this->autoDelete = $autoDelete;
        return $this;
    }

    public function isNowait(): bool
    {
        return $this->nowait;
    }

    /**
     * @return static
     */
    public function setNowait(bool $nowait): self
    {
        $this->nowait = $nowait;
        return $this;
    }

    /**
     * @return AMQPTable|array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param AMQPTable|array $arguments
     * @return static
     */
    public function setArguments($arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param null|int $ticket
     * @return static
     */
    public function setTicket($ticket): self
    {
        $this->ticket = $ticket;
        return $this;
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

    /**
     * @throws AMQPProtocolChannelException when the channel operation is failed
     */
    public function declare(Message $message, ?AMQPChannel $channel = null, bool $release = false): void
    {
        try {
            if (!$channel) {
                /** @var Connection $connection */
                $connection = $this->amqp($message->getPoolName());
                $channel = $connection->getChannel();
            }

            $builder = $message->getExchangeBuilder();

            $channel->exchange_declare($builder->getExchange(), $builder->getType(), $builder->isPassive(),
                $builder->isDurable(), $builder->isAutoDelete(), $builder->isInternal(), $builder->isNowait(),
                $builder->getArguments(), $builder->getTicket());
        } finally {
            if (isset($connection) && $release) {
                $connection->release();
            }
        }
    }
}