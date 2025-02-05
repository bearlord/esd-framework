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

    private $_lastQuery;

    /**
     * Redis constructor.
     */
    public function __construct()
    {
        $this->_redis = new \Redis();
    }

    /**
     * @param string $name
     * @param array|null $arguments
     * @return mixed
     */
    public function __call(string $name, ?array $arguments = null)
    {
        $this->_lastQuery = $name;
        return $this->execute($name, function () use ($name, $arguments) {
            return call_user_func_array([$this->_redis, $name], $arguments);
        });
    }

    /**
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value)
    {
        $this->_redis->$name = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->_redis->$name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return "redis";
    }

    /**
     * @param string $name
     * @param callable|null $call
     * @return mixed
     */
    public function execute(string $name, ?callable $call = null)
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

    public function close()
    {

    }
}
