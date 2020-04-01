<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT\Auth;

interface MqttAuth
{
    /**
     * @param $fd
     * @param $username
     * @param $password
     * @return mixed
     */
    public function auth($fd, $username, $password): array;
}