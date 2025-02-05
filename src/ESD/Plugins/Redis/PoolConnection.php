<?php

namespace ESD\Plugins\Redis;

use ESD\Core\Pool\ConnectionInterface;
use ESD\Core\Pool\Exception\ConnectionException;
use ESD\Core\Pool\Connection as CorePoolConnection;
use ESD\Plugins\Redis\Exception\InvalidRedisConnectionException;
use ESD\Plugins\Redis\Exception\InvalidRedisOptionException;
use ESD\Server\Coroutine\Server;
use RedisCluster;
use RedisSentinel;
use Redis;

class PoolConnection extends CorePoolConnection
{
    /**
     * @var \ESD\Plugins\Redis\RedisConnection
     */
    protected $connection;

    /**
     * @var \ESD\Plugins\Redis\RedisPool
     */
    protected $pool;

    /**
     * @var \ESD\Plugins\Redis\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $configArray = [];

    /**
     * @param \ESD\Plugins\Redis\RedisPool $pool
     * @param \ESD\Plugins\Redis\Config $config
     */
    public function __construct(RedisPool $pool, Config $config)
    {
        parent::__construct($pool, $config);

        $this->configArray = $config->buildConfig();
    }

    /**
     * @return \ESD\Plugins\Redis\RedisConnection
     */
    public function getConnection(): RedisConnection
    {
        return $this->connection;
    }

    /**
     * @param \ESD\Plugins\Redis\RedisConnection $connection
     * @return void
     */
    public function setConnection(RedisConnection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return $this
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     */
    public function getActiveConnection()
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
     * @param string|null $name
     * @return string
     */
    protected function formatOptionName(?string $name = null): string
    {
        if (empty($name)) {
            return "";
        }
        $optionNmae = null;

        switch ($name) {
            case "serializer":
                $optionNmae = Redis::OPT_SERIALIZER;
                break;
            case "prefix":
                $optionNmae = Redis::OPT_PREFIX;
                break;
            case "readTimeout":
                $optionNmae = Redis::OPT_READ_TIMEOUT;
                break;
            case "scan":
                $optionNmae = Redis::OPT_SCAN;
                break;
            case "failover":
                $optionNmae = Redis::OPT_FAILOVER;
                break;
            case "keepalive":
                $optionNmae = defined(Redis::class . '::OPT_SLAVE_FAILOVER') ? Redis::OPT_SLAVE_FAILOVER : 5;
                break;
            case "compression":
                $optionNmae = Redis::OPT_COMPRESSION;
                break;
            case "replyLiteral":
                $optionNmae = Redis::OPT_REPLY_LITERAL;
                break;
            case "compressionLevel":
                $optionNmae = Redis::OPT_COMPRESSION_LEVEL;
                break;
            default:
                throw new InvalidRedisOptionException(sprintf('The redis option key `%s` is invalid.', $name));
                break;
        }

        return $optionNmae;
    }

    /**
     * @return bool
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
     */
    public function reconnect(): bool
    {
        $this->close();
        $this->connect();
        return true;
    }

    /**
     * @return bool
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
     * @return \ESD\Plugins\Redis\RedisConnection
     * @throws \ESD\Core\Pool\Exception\ConnectionException
     */
    public function getDbConnection()
    {
        try {
            $activeConnection = $this->getActiveConnection();
            $connection = $activeConnection->getConnection();
            return $connection;
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->warning('Get connection failed, try again. ' . (string)$exception);

            $activeConnection = $this->getActiveConnection();
            return $activeConnection->getConnection();
        }
    }

}
