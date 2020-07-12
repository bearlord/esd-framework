<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class AmqpConnection
 * @package ESD\Plugins\Amqp
 */
class AmqpConnection
{

    /**
     * @var AmqpConfig
     */
    protected $amqpPoolConfig;

    /**
     * @var AMQPStreamConnection
     */
    protected $connection = null;

    /**
     * AmqpConnection constructor.
     * @param AmqpPoolConfig $amqpPoolConfig
     * @throws Exception
     */
    public function __construct(AmqpPoolConfig $amqpPoolConfig)
    {
        $amqpPoolConfig->buildConfig();
        $this->amqpPoolConfig = $amqpPoolConfig;
    }

    /**
     * Get channel
     * @param null $channel_id
     * @return AmqpChannel
     * @throws Exception
     */
    public function channel($channel_id = null)
    {
        $this->connect();
        return $this->connection->channel($channel_id);
    }


    /**
     * Connect
     * @throws Exception
     */
    protected function connect()
    {
        if($this->connection && !$this->connection->isConnected()) {
            $this->connection->reconnect();
        } else if (!$this->connection) {
            /**
             * @var $connection AmqpConnection
             */
            $connection = AMQPStreamConnection::create_connection($this->amqpPoolConfig->getHosts(), [
                'insist' => $this->amqpPoolConfig->isInsist(),
                'login_method' => $this->amqpPoolConfig->getLoginMethod(),
                'login_response' => $this->amqpPoolConfig->getLoginResponse(),
                'locale' => $this->amqpPoolConfig->getLocale(),
                'connection_timeout' => $this->amqpPoolConfig->getConnectionTimeout(),
                'read_write_timeout' => $this->amqpPoolConfig->getReadWriteTimeout(),
                'context' => $this->amqpPoolConfig->getContext(),
                'keepalive' => $this->amqpPoolConfig->isKeepAlive(),
                'heartbeat' => $this->amqpPoolConfig->getHeartBeat()
            ]);
            $this->connection = $connection;
        }
    }

    /**
     * @return AmqpPoolConfig
     */
    public function getAmqpPoolConfig(): AmqpPoolConfig
    {
        return $this->amqpPoolConfig;
    }

    /**
     * @param AmqpPoolConfig $amqpPoolConfig
     */
    public function setAmqpPoolConfig(AmqpPoolConfig $amqpPoolConfig): void
    {
        $this->amqpPoolConfig = $amqpPoolConfig;
    }
}