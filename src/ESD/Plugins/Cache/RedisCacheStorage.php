<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cache;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Redis\GetRedis;

class RedisCacheStorage implements CacheStorage
{
    use GetRedis;
    use GetLogger;

    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    const PREFIX = "CACHE_";

    /**
     * RedisCacheStorage constructor.
     * @param CacheConfig $cacheConfig
     */
    public function __construct(CacheConfig $cacheConfig)
    {
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * @param string $nameSpace
     * @param string $id
     * @return mixed
     */
    public function getFromNameSpace(string $nameSpace, string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->hGet(self::PREFIX . $nameSpace, $id);
    }

    /**
     * @param string $nameSpace
     * @param string $id
     * @param string $data
     * @return mixed
     */
    public function setFromNameSpace(string $nameSpace, string $id, string $data)
    {
        return $this->redis($this->cacheConfig->getDb())->hSet(self::PREFIX . $nameSpace, $id, $data);
    }

    /**
     * @param string $nameSpace
     * @param string $id
     * @return mixed
     */
    public function removeFromNameSpace(string $nameSpace, string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->hDel(self::PREFIX . $nameSpace, $id);
    }

    /**
     * @param string $nameSpace
     * @return mixed
     */
    public function removeNameSpace(string $nameSpace)
    {
        return $this->redis($this->cacheConfig->getDb())->del(self::PREFIX . $nameSpace);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->redis($this->cacheConfig->getDb())->get(self::PREFIX . $id);
    }

    /**
     * @param string $id
     * @param string $data
     * @param int $time
     * @return mixed
     */
    public function set(string $id, string $data, int $time)
    {
        //If the cache time is the default value, add a 0-20% float to the cache time to avoid a large number of caches expire
        if ($time == 0) {
            $time = $this->cacheConfig->getTimeout();
            $time = mt_rand($time, ceil($time *0.2) + $time);
        }
        if ($time > 0) {
            return $this->redis($this->cacheConfig->getDb())->setex(self::PREFIX . $id, $time, $data);
        } else {
            return $this->redis($this->cacheConfig->getDb())->set(self::PREFIX . $id, $data);
        }
    }

    /**
     * @param string $id
     * @return mixed|void
     */
    public function remove(string $id)
    {
        $this->redis($this->cacheConfig->getDb())->del(self::PREFIX . $id);
    }

    /**
     * @param string $resource
     * @param int $ttl
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function lock(string $resource, int $ttl = 1000)
    {
        $resource = 'LOCK_' . $resource;
        $token = uniqid();
        if($this->redis($this->cacheConfig->getDb())->set($resource, $token, ['NX', 'PX' => $ttl])){
            $this->debug("cache lock:" . $resource .', token :'. $token);
            return $token;
        }
        $this->debug("cache lock fail" . $resource);
        return false;
    }

    /**
     * @param string $resource
     * @param string $token
     * @return mixed
     * @throws \Exception
     */
    public function unlock(string $resource, string $token)
    {
        $resource = 'LOCK_' . $resource;
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        $result =  $this->redis($this->cacheConfig->getDb())->eval($script, [$resource, $token], 1);
        $this->debug('cache unlock :' . $resource . ', token:'.$token. ', result:'.$result);
        return $result;
    }

}