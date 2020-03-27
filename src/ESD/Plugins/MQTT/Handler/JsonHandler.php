<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/13
 * Time: 18:01
 */

namespace ESD\Plugins\MQTT\Handler;
class JsonHandler implements Handler
{
    public function pack($data): string
    {
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    public function upPack($data)
    {
        return json_decode($data, true);
    }
}