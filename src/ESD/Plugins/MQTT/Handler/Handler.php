<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/6/13
 * Time: 18:02
 */

namespace ESD\Plugins\MQTT\Handler;


interface Handler
{
    public function pack($data): string;

    public function upPack($data);
}