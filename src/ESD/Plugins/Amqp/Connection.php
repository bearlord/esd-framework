<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use Exception;
use AMQPConnection;

/**
 * Class Connection
 * @package ESD\Plugins\Amqp
 */
class Connection
{

    /**
     * @var AmqpConfig
     */
    protected $config;

    /**
     * @var AMQPConnection
     */
    protected $connection = null;


    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
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
     * AmqpConnection constructor.
     * @param Config $config
     * @throws Exception
     */
    public function __construct(Config $config)
    {
        $config->buildConfig();
        $this->config = $config;
        $this->connect();
    }

    /**
     * Get channel
     * @param null $channel_id
     * @return \AmqpChannel
     * @throws Exception
     */
    public function channel($channel_id = null)
    {
        return new \AMQPChannel($this->connection, $channel_id);
    }


    /**
     * Connect
     * @throws Exception
     */
    public function connect(): void
    {
        if($this->connection && !$this->connection->isConnected()) {
            $this->connection->reconnect();
        } else if (!$this->connection) {
            foreach ($this->config->getHosts() as $key => $value) {
                $connection = new \AMQPConnection($value);
                try {
                    $connection->connect();
                    if ($connection->isConnected()) {
                        $this->setConnection($connection);
                    }
                    break;
                } catch (\Exception $exception) {
                    throw $exception;
                }
            }
        }
    }
}