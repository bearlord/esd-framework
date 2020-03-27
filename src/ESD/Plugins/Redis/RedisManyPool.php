<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use Redis;

class RedisManyPool
{
    protected $poolList = [];

    /**
     * Get pool
     *
     * @param $name
     * @return RedisPool|null
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * Add pool
     *
     * @param RedisPool $redisPool
     */
    public function addPool(RedisPool $redisPool)
    {
        $this->poolList[$redisPool->getRedisConfig()->getName()] = $redisPool;
    }

    /**
     * @return Redis
     * @throws RedisException
     */
    public function db(): Redis
    {
        $default = $this->getPool();
        if ($default == null) {
            throw new RedisException("No default redis configuration is set");
        }
        return $default->db();
    }
}