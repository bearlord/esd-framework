<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Session;

use ESD\Plugins\Redis\GetRedis;

/**
 * Class RedisSessionStorage
 * @package ESD\Plugins\Session
 */
class RedisSessionStorage implements SessionStorage
{
    use GetRedis;
    /**
     * @var SessionConfig
     */
    private $sessionConfig;

    /**
     * @var array
     */
    private $redisConfig;

    const prefix = "SESSION_";

    /**
     * RedisSessionStorage constructor.
     * @param SessionConfig $sessionConfig
     */
    public function __construct(SessionConfig $sessionConfig)
    {
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * @param string $id
     * @return bool|mixed|string
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function get(string $id)
    {
        $redis = $this->redis();
        $redis->select($this->sessionConfig->getDatabase());
        return $redis->get(self::prefix . $id);
    }

    /**
     * @param string $id
     * @param string $data
     * @return mixed|void
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function set(string $id, string $data)
    {
        $redis = $this->redis();
        $redis->select($this->sessionConfig->getDatabase());
        return $redis->setex(self::prefix . $id, $this->sessionConfig->getTimeout(), $data);
    }

    /**
     * @param string $id
     * @return mixed|void
     * @throws \ESD\Plugins\Redis\RedisException
     */
    public function remove(string $id)
    {
        $redis = $this->redis();
        $redis->select($this->sessionConfig->getDatabase());
        return $redis->del(self::prefix . $id);
    }
}