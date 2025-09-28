<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Auth;

interface MqttAuthInterface
{
    public function auth(int $fd, string $username, string $password): array;
}
