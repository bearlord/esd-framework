<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/13
 * Time: 10:49
 */

namespace ESD\Plugins\MQTT\Auth;


class EasyMqttAuth implements MqttAuth
{

    /**
     * 返回结果[$isAuth,$uid]
     * @param $fd
     * @param $username
     * @param $password
     * @return mixed
     */
    public function auth($fd, $username, $password): array
    {
        return ["true", $fd];
    }
}