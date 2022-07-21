<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Server\Server;
use ESD\Coroutine\Coroutine;
use ESD\Plugins\Amqp\Connection\AMQPSwooleConnection;
use ESD\Plugins\Amqp\Connection\KeepaliveIO;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

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
     * @var AMQPStreamConnection
     */
    protected $connection = null;

    /**
     * @var float
     */
    protected $lastHeartbeatTime = 0.0;

    /**
     * @var null|\PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * @var null|AMQPChannel
     */
    protected $confirmChannel;

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
     * @return
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param AMQPConnection $connection
     */
    public function setConnection($connection)
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
     * Connect
     *
     * @throws Exception
     */
    public function connect(): void
    {
        $class = AMQPStreamConnection::class;
        if (Coroutine::getCid() > 0) {
            $class = AMQPSwooleConnection::class;
        }

        $this->lastHeartbeatTime = microtime(true);

        /** @var AbstractConnection $connection */
        $connection = new $class(
            $this->config->getHost() ?? 'localhost',
            $this->config->getPort() ?? 5672,
            $this->config->getUser() ?? 'guest',
            $this->config->getPassword() ?? 'guest',
            $this->config->getVhost() ?? '/',
            $this->config->isInsist(),
            $this->config->getLoginMethod(),
            $this->config->getLoginResponse(),
            $this->config->getLocale(),
            $this->config->getConnectionTimeout(),
            $this->config->getReadWriteTimeout(),
            $this->config->getContext(),
            $this->config->isKeepalive(),
            $this->config->getHeartbeat()
        );

        $this->connection = $connection;
    }

    /**
     * Get active connection
     *
     * @return AbstractConnection
     * @throws Exception
     */
    public function getActiveConnection(): AbstractConnection
    {
        if ($this->check()) {
            return $this->connection;
        }
        $this->reconnect();

        return $this->connection;
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        if (!$this->channel || !$this->check()) {
            $this->channel = $this->getConnection()->channel();
        }
        return $this->channel;
    }

    /**
     * @return AMQPChannel
     */
    public function getConfirmChannel(): AMQPChannel
    {
        if (!$this->confirmChannel || !$this->check()) {
            $this->confirmChannel = $this->getConnection()->channel();
            $this->confirmChannel->confirm_select();
        }
        return $this->confirmChannel;
    }

    /**
     * Reconnect
     *
     * @return bool
     * @throws Exception
     */
    public function reconnect(): bool
    {
        if ($this->connection && $this->connection->getIO() instanceof KeepaliveIO) {
            $this->connection->getIO()->close();
        }

        $this->connection = $this->connect();
        $this->channel = null;
        $this->confirmChannel = null;
        return true;

//        if ($this->connection) {
//            $this->connection->close();
//        }
//        $this->connect();
//        return true;
    }

    public function close(): bool
    {
        try {
            if ($connection = $this->connection) {
                if ($connection->getIO() instanceof KeepaliveIO) {
                    $connection->getIO()->close();
                }

                $connection->close();
            }
        } catch (AMQPRuntimeException $exception) {
            Server::$instance->getLog()->warning((string) $exception);
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->error((string) $exception);
        } finally {
            $this->connection = null;
        }

        $this->channel = null;
        $this->confirmChannel = null;
        return true;
    }

    /**
     * Check
     *
     * @return bool
     */
    public function check(): bool
    {
        $result = isset($this->connection) && $this->connection instanceof AbstractConnection && $this->connection->isConnected() && !$this->isHeartbeatTimeout();
        if ($result) {
            // The connection is valid, reset the last heartbeat time.
            $currentTime = microtime(true);
            $this->lastHeartbeatTime = $currentTime;
        }

        return $result;
    }

    /**
     * Is heaertbeat timeout
     *
     * @return bool
     */
    protected function isHeartbeatTimeout(): bool
    {
        if ($this->config->getHeartbeat() === 0) {
            return false;
        }

        if (microtime(true) - $this->lastHeartbeatTime > $this->config->getHeartbeat()) {
            return true;
        }

        return false;
    }

    /**
     * __call
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }
}