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
    protected $config;

    /**
     * @var AMQPStreamConnection
     */
    protected $connection = null;

    /**
     * AmqpConnection constructor.
     * @param Config $config
     * @throws Exception
     */
    public function __construct(Config $config)
    {
        $config->buildConfig();
        $this->config = $config;
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
            $connection = AMQPStreamConnection::create_connection($this->config->getHosts(), [
                'insist' => $this->config->isInsist(),
                'login_method' => $this->config->getLoginMethod(),
                'login_response' => $this->config->getLoginResponse(),
                'locale' => $this->config->getLocale(),
                'connection_timeout' => $this->config->getConnectionTimeout(),
                'read_write_timeout' => $this->config->getReadWriteTimeout(),
                'context' => $this->config->getContext(),
                'keepalive' => $this->config->isKeepAlive(),
                'heartbeat' => $this->config->getHeartBeat()
            ]);
            $this->connection = $connection;
        }
    }

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
}