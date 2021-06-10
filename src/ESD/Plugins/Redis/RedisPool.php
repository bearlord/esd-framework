<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Channel\Channel;
use ESD\Core\Pool\Pool;

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
        $this->config = $config;
        $config->buildConfig();
        $this->pool = DIGet(Channel::class, [$config->getPoolMaxNumber()]);
        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = new Redis();
            $this->pool->push($db);
        }
    }

    /**
     * @return \Redis
     * @throws RedisException
     */
    public function db(): Redis
    {
        $contextKey = sprintf("Redis:%s", $this->getConfig()->getName());
        $db = getContextValue($contextKey);
        if ($db == null) {
            /** @var Redis|\Redis $db */
            $db = $this->pool->pop();
            if ($db instanceof Redis) {
                if (!$db->isConnected()) {
                    if (!$db->connect($this->config->getHost(), $this->config->getPort())) {
                        throw new RedisException($db->getLastError());
                    }

                    $db->setOption(\Redis::OPT_READ_TIMEOUT, -1);

                    if (!empty($this->config->getPassword())) {
                        if (!$db->auth($this->config->getPassword())) {
                            throw new RedisException($db->getLastError());
                        }
                    }

                    $db->select($this->config->getDatabase());
                }
            }
            \Swoole\Coroutine::defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }
}