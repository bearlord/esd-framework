<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server;


class EmptyServer
{
    public function __call($name, $arguments)
    {
        var_dump("__call:" . $name);
    }

    public function __get($name)
    {
        var_dump("__get:" . $name);
    }

    public function __set($name, $value)
    {
        var_dump("__set:" . $name);
    }
}