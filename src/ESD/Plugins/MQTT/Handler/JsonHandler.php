<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Handler;

/**
 * Class JsonHandler
 * @package ESD\Plugins\MQTT\Handler
 */
class JsonHandler implements Handler
{
    /**
     * @param $data
     * @return string
     */
    public function pack($data): string
    {
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function upPack($data)
    {
        return json_decode($data, true);
    }
}