<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack;

/**
 * Class ClientDataProxy
 * @package ESD\Plugins\Pack
 */
class ClientDataProxy
{
    use GetClientData;

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getClientData()->$name;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->getClientData()->$name = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        if ($this->getClientData() == null) {
            return null;
        }
        return call_user_func_array([$this->getClientData(), $name], $arguments);
    }
}