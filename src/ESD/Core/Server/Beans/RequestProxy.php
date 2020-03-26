<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

/**
 * Class RequestProxy
 * @package ESD\Core\Server\Beans
 */
class RequestProxy
{
    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return getDeepContextValueByClassName(Request::class)->$name;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        getDeepContextValueByClassName(Request::class)->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([getDeepContextValueByClassName(Request::class), $name], $arguments);
    }
}