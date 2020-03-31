<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Session;

/**
 * Class HttpSessionProxy
 * @package ESD\Plugins\Session
 */
class HttpSessionProxy
{
    use GetSession;

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getSession()->$name;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->getSession()->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getSession(), $name], $arguments);
    }
}