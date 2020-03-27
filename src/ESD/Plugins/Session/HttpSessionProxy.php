<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 9:19
 */

namespace ESD\Plugins\Session;


class HttpSessionProxy
{
    use GetSession;

    public function __get($name)
    {
        return $this->getSession()->$name;
    }

    public function __set($name, $value)
    {
        $this->getSession()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getSession(), $name], $arguments);
    }
}