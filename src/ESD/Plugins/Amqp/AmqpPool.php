<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

/**
 * Class AmqpPool
 * @package ESD\Plugins\Amqp
 */
class AmqpPool
{
    protected $poolList = [];

    /**
     * Add amqp connection
     *
     * @param Connection $connection
     */
    public function addConnection(Connection $connection)
    {
        $this->poolList[$connection->getConfig()->getName()] = $connection;
    }

    /**
     * Get channel
     *
     * @param string $name
     * @param int $channel_id
     * @return AMQPChannel
     * @throws \Exception
     */
    public function channel($name = "default", $channel_id = null): AMQPChannel
    {
        $connection = $this->getConnection($name);
        return new \AMQPChannel($connection, $channel_id);
    }

    /**
     * Get connection
     * 
     * @param $name
     * @return AmqpConnection|null
     */
    public function getConnection($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }
}