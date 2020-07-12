<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Psr\DB\DBInterface;

class AmqpProxy implements DBInterface
{
    use GetAmqp;

    protected $_lastQuery;

    /**
     * @param $name
     * @return mixed
     * @throws AmqpException
     * @throws \ReflectionException
     */
    public function __get($name)
    {
        return $this->amqp()->$name;
    }

    /**
     * @param $name
     * @param $value
     * @throws AmqpException
     * @throws \ReflectionException
     */
    public function __set($name, $value)
    {
        $this->amqp()->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        $this->_lastQuery = json_encode($arguments);
        return $this->execute($name, function () use ($name, $arguments) {
            return call_user_func_array([$this->amqp(), $name], $arguments);
        });
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return 'amqp';
    }

    /**
     * @param $name
     * @param callable|null $call
     * @return mixed|null
     */
    public function execute($name, callable $call = null)
    {
        if ($call != null) {
            return $call();
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getLastQuery()
    {
        return $this->_lastQuery;
    }
}