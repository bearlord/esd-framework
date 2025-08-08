<?php

namespace ESD\Plugins\Redis;

use ESD\Core\Pool\ConfigInterface;
use ESD\Core\Pool\Exception\ConnectionException;
use ESD\Core\Pool\Connection as CorePoolConnection;
use ESD\Plugins\Redis\Exception\InvalidRedisOptionException;
use ESD\Server\Coroutine\Server;
use Redis;

class PoolConnection extends CorePoolConnection
{
    /**
     * @var RedisConnection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $configArray = [];

    /**
     * @param RedisPool $pool
     * @param ConfigInterface $config
     * @throws RedisException
     */
    public function __construct(RedisPool $pool, ConfigInterface $config)
    {
        parent::__construct($pool, $config);

        $this->configArray = $config->buildConfig();
    }

    /**
     * @return RedisConnection
     */
    public function getConnection(): RedisConnection
    {
        return $this->connection;
    }

    /**
     * @param RedisConnection $connection
     * @return void
     */
    public function setConnection(RedisConnection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return $this
     * @throws ConnectionException
     * @throws RedisException
     * @throws \RedisClusterException
     * @throws \RedisException
     */
    public function getActiveConnection(): PoolConnection
    {
        if ($this->check()) {
            return $this;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * @return bool
     * @throws ConnectionException
     * @throws RedisException
     * @throws \RedisClusterException
     * @throws \RedisException
     */
    public function connect(): bool
    {
        $connectionHandle = new RedisConnection($this->configArray);
        $connectionHandle->open();

        $this->setConnection($connectionHandle);
        $this->setLastUseTime(microtime(true));

        return true;
    }

    /**
     * @return bool
     * @throws ConnectionException
     * @throws RedisException
     * @throws \RedisClusterException
     * @throws \RedisException
     * @throws \Exception
     */
    public function reconnect(): bool
    {
        $this->close();
        $this->connect();
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function close(): bool
    {
        if ($this->connection instanceof RedisConnection) {
            $this->connection->close();
        }

        unset($this->connection);

        return true;
    }

    /**
     * @return RedisConnection
     * @throws ConnectionException
     */
    public function getDbConnection(): RedisConnection
    {
        try {
            $activeConnection = $this->getActiveConnection();
            return $activeConnection->getConnection();
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->warning('Get connection failed, try again. ' . (string)$exception);

            $activeConnection = $this->getActiveConnection();
            return $activeConnection->getConnection();
        }
    }

}
