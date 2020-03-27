<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;


use ESD\Psr\DB\DBInterface;

class Redis implements DBInterface
{
    /**
     * @var \Redis
     */
    private $_redis;

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
     * @return mixed|string
     */
    public function getType()
    {
        return "redis";
    }

    /**
     * @param $name
     * @param callable|null $call
     * @return mixed
     */
    public function execute($name,callable $call = null)
    {
        if ($call != null) {
            return $call();
        }
    }

    /**
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->_lastQuery;
    }
}