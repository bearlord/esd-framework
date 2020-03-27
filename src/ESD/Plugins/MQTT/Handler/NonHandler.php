<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/13
 * Time: 18:01
 */
namespace ESD\Plugins\MQTT\Handler;
class NonHandler implements Handler
{

    public function pack($data): string
    {
        return $data."";
    }

    public function upPack($data)
    {
       return $data;
    }
}