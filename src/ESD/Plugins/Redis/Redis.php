<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Psr\DB\DBInterface;

/**
 * Class Redis
 * @package ESD\Plugins\Redis
 */
class Redis implements DBInterface
{
    /**
     * @var \Redis
     */
    private $_redis;

    /**
     * @var string
     */
    private $_lastQuery;

    /**
     * Redis constructor.
     */
    public function __construct()
    {
        $this->_redis = new \Redis();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->_lastQuery = $name;
        return $this->execute($name, function () use ($name, $arguments) {
            return call_user_func_array([$this->_redis, $name], $arguments);
        });
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->_redis->$name = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_redis->$name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return "redis";
    }

    /**
     * @param string $name
     * @param callable|null $call
     * @return mixed
     */
    public function execute(string $name, callable $call = null): mixed
    {
        if ($call != null) {
            return $call();
        }
    }

    /**
     * @return string
     */
    public function getLastQuery(): string
    {
        return $this->_lastQuery;
    }
}