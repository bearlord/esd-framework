<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Yii\Base\Component;
use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use AMQPEnvelope;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Db\Exception;

/**
 * Class Handle
 * @package ESD\Plugins\Amqp
 */
class Handle
{
    /**
     * @var AMQPConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * @var AMQPQueue;
     */
    protected $queue;

    /**
     * Context constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (empty($config['connection'])) {
            throw new InvalidArgumentException('Invalid Argument connection');
        }

        $this->connection = $config['connection'];
        $this->preInit();
    }

    /**
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public function preInit()
    {
        if (!empty($this->connection)) {
            //Set channel
            $this->setChannel(new AMQPChannel($this->connection));

            //Set exchange
            $this->setExchange(new AMQPExchange($this->getChannel()));

            //Set queue
            $this->setQueue(new AMQPQueue($this->getChannel()));
        }
    }

    /**
     * @return AMQPConnection
     */
    public function getConnection(): AMQPConnection
    {
        return $this->connection;
    }

    /**
     * @param AMQPConnection $connection
     */
    public function setConnection(AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function setChannel(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return AMQPExchange
     */
    public function getExchange(): AMQPExchange
    {
        return $this->exchange;
    }

    /**
     * @param AMQPExchange $exchange
     */
    public function setExchange(AMQPExchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @return AMQPQueue
     */
    public function getQueue(): AMQPQueue
    {
        return $this->queue;
    }

    /**
     * @param AMQPQueue $queue
     */
    public function setQueue(AMQPQueue $queue)
    {
        $this->queue = $queue;
    }
}