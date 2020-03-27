<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/7
 * Time: 17:09
 */

namespace ESD\Plugins\Session;


use ESD\Plugins\Redis\GetRedis;

class RedisSessionStorage implements SessionStorage
{
    use GetRedis;
    /**
     * @var SessionConfig
     */
    private $sessionConfig;

    const prefix = "SESSION_";

    public function __construct(SessionConfig $sessionConfig)
    {
        $this->sessionConfig = $sessionConfig;
    }

    public function get(string $id)
    {
        return $this->redis($this->sessionConfig->getDb())->get(self::prefix . $id);
    }

    public function set(string $id, string $data)
    {
        $this->redis($this->sessionConfig->getDb())->setex(self::prefix . $id, $this->sessionConfig->getTimeout(), $data);
    }

    public function remove(string $id)
    {
        $this->redis($this->sessionConfig->getDb())->del(self::prefix . $id);
    }
}