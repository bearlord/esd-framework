<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Channel\Channel;
use ESD\Core\Pool\ConnectionInterface;
use ESD\Core\Pool\DefaultFrequency;
use ESD\Core\Pool\Pool;
use ESD\Server\Coroutine\Server;

/**
 * Class RedisPool
 * @package ESD\Plugins\Redis
 */
class RedisPool extends Pool
{
    /**
     * RedisPool constructor.
     * @param Config $config
     * @throws RedisException
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $this->frequency = new DefaultFrequency($this);


        $this->config = $config;
        $config->buildConfig();
        $this->pool = DIGet(Channel::class, [$config->getPoolMaxNumber()]);
        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = new Redis();
            $this->pool->push($db);
        }
    }

    /**
     * @return \ESD\Core\Pool\ConnectionInterface
     */
    protected function createConnection(): ConnectionInterface
    {
        $connection = new PoolConnection($this, $this->getConfig());
        $connection->connect();

        return $connection;
    }

    /**
     * @return \ESD\Plugins\Redis\RedisConnection
     * @throws \RuntimeException
     */
    public function db(): RedisConnection
    {
        $contextKey = sprintf("Redis:%s", $this->getConfig()->getName());

        $db = getContextValue($contextKey);
        if ($db == null) {
            /** @var \ESD\Plugins\Redis\PoolConnection $poolConnection */
            $poolConnection = $this->get();
            if (empty($poolConnection)) {
                $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase maxConnections";
                Server::$instance->getLog()->error($errorMessage);
                throw new \RuntimeException($errorMessage);
            }

            /** @var \Redis $db */
            $db = $poolConnection->getDbConnection();

            \Swoole\Coroutine::defer(function () use ($poolConnection, $contextKey) {
                $db = getContextValue($contextKey);

                $poolConnection->setLastUseTime(microtime(true));
                $poolConnection->setConnection($db);

                $this->release($poolConnection);
            });
            setContextValue($contextKey, $db);
        }

        if (! $db instanceof \ESD\Plugins\Redis\RedisConnection) {
            $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase maxConnections";
            Server::$instance->getLog()->error($errorMessage);
            throw new \RuntimeException($errorMessage);
        }

        return $db;
    }
}
