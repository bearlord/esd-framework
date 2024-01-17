<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
