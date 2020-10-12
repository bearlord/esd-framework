<?php


namespace ESD\Yii\Queue\Drivers\Amqp;


use ESD\Yii\Base\Component;
use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use AMQPEnvelope;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Db\Exception;

/**
 * Class Context
 * @package ESD\Yii\Queue\Drivers\Amqp
 */
class Context extends Component
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
        parent::__construct($config);
    }

    
    public function init()
    {
        if (!empty($this->connection)) {
            //Set channel
            $this->setChannel(new AMQPChannel($this->connection));

            //Set queue
            $this->setQueue(new AMQPQueue($this->getChannel()));

            //Set exchage
            $this->setExchange(new AMQPExchange($this->getChannel()));
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