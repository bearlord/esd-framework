<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

/**
 * Trait GetRedis
 * @package ESD\Plugins\Redis
 */
trait GetRedis
{
    /**
     * @param string $name
     * @return mixed|\Redis
     * @throws RedisException
     */
    public function redis($name = "default")
    {
        $db = getContextValue("Redis:$name");
        if ($db == null) {
            /** @var RedisManyPool $redisPool */
            $redisPool = getDeepContextValueByClassName(RedisManyPool::class);
            $pool = $redisPool->getPool($name);
            if ($pool == null) throw new RedisException("Redis connection pool named {$name} was not found");
            return $pool->db();
        } else {
            return $db;
        }
    }
}