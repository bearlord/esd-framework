<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Handler;

/**
 * Class NonHandler
 * @package ESD\Plugins\MQTT\Handler
 */
class NonHandler implements Handler
{
    /**
     * @param $data
     * @return string
     */
    public function pack($data): string
    {
        return $data . "";
    }

    /**
     * @param $data
     * @return mixed
     */
    public function upPack($data)
    {
        return $data;
    }
}