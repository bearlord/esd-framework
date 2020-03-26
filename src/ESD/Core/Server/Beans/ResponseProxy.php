<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

/**
 * Class ResponseProxy
 * @package ESD\Core\Server\Beans
 */
class ResponseProxy
{
    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return getDeepContextValueByClassName(Response::class)->$name;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        getDeepContextValueByClassName(Response::class)->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([getDeepContextValueByClassName(Response::class), $name], $arguments);
    }
}