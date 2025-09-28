<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Auth;

class MqttAuth implements MqttAuthInterface
{

    /**
     * @param int $fd
     * @param string $username
     * @param string $password
     * @return array
     */
    public function auth(int $fd, string $username, string $password): array
    {
        return ["true", $fd];
    }
}
