<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/13
 * Time: 10:41
 */

namespace ESD\Plugins\MQTT\Auth;


interface MqttAuth
{
    /**
     * 返回结果[$isAuth,$uid]
     * @param $fd
     * @param $username
     * @param $password
     * @return mixed
     */
    public function auth($fd, $username, $password): array;
}