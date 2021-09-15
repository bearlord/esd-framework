<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Packet;

use ESD\Plugins\MQTT\Protocol\Types;
use ESD\Plugins\MQTT\Tools\UnPackTool;

class UnPackV3
{
    /**
     * @param string $remaining
     * @return array
     */
    public static function connect(string $remaining): array
    {
        $protocolName = UnPackTool::string($remaining);
        $protocolLevel = ord($remaining[0]);
        $cleanSession = ord($remaining[1]) >> 1 & 0x1;
        $willFlag = ord($remaining[1]) >> 2 & 0x1;
        $willQos = ord($remaining[1]) >> 3 & 0x3;
        $willRetain = ord($remaining[1]) >> 5 & 0x1;
        $passwordFlag = ord($remaining[1]) >> 6 & 0x1;
        $userNameFlag = ord($remaining[1]) >> 7 & 0x1;
        $remaining = substr($remaining, 2);
        $keepAlive = UnPackTool::shortInt($remaining);
        $clientId = UnPackTool::string($remaining);
        if ($willFlag) {
            $willTopic = UnPackTool::string($remaining);
            $willMessage = UnPackTool::string($remaining);
        }
        $userName = $password = '';
        if ($userNameFlag) {
            $userName = UnPackTool::string($remaining);
        }
        if ($passwordFlag) {
            $password = UnPackTool::string($remaining);
        }
        $package = [
            'type' => Types::CONNECT,
            'protocol_name' => $protocolName,
            'protocol_level' => $protocolLevel,
            'clean_session' => $cleanSession,
            'will' => [],
            'user_name' => $userName,
            'password' => $password,
            'keep_alive' => $keepAlive,
            'client_id' => $clientId,
        ];
        if ($willFlag) {
            $package['will'] = [
                'qos' => $willQos,
                'retain' => $willRetain,
                'topic' => $willTopic,
                'message' => $willMessage,
            ];
        } else {
            unset($package['will']);
        }

        return $package;
    }

    /**
     * @param string $remaining
     * @return array
     */
    public static function connAck(string $remaining): array
    {
        return ['type' => Types::CONNACK, 'session_present' => ord($remaining[0]) & 0x01, 'code' => ord($remaining[1])];
    }

    /**
     * @param int $dup
     * @param int $qos
     * @param int $retain
     * @param string $remaining
     * @return array
     */
    public static function publish(int $dup, int $qos, int $retain, string $remaining): array
    {
        $topic = UnPackTool::string($remaining);
        if ($qos) {
            $messageId = UnPackTool::shortInt($remaining);
        }
        $package = [
            'type' => Types::PUBLISH,
            'dup' => $dup,
            'qos' => $qos,
            'retain' => $retain,
            'topic' => $topic,
            'message' => $remaining,
        ];
        if ($qos) {
            $package['message_id'] = $messageId;
        }

        return $package;
    }

    /**
     * @param string $remaining
     * @return array
     */
    public static function subscribe(string $remaining): array
    {
        $messageId = UnPackTool::shortInt($remaining);
        $topics = [];
        while ($remaining) {
            $topic = UnPackTool::string($remaining);
            $qos = UnPackTool::byte($remaining);
            $topics[$topic]['qos'] = $qos;
        }

        return ['type' => Types::SUBSCRIBE, 'message_id' => $messageId, 'topics' => $topics];
    }

    /**
     * @param string $remaining
     * @return array
     */
    public static function subAck(string $remaining): array
    {
        $messageId = UnPackTool::shortInt($remaining);
        $codes = unpack('C*', $remaining);

        return ['type' => Types::SUBACK, 'message_id' => $messageId, 'codes' => array_values($codes)];
    }

    /**
     * @param string $remaining
     * @return array
     */
    public static function unSubscribe(string $remaining): array
    {
        $messageId = UnPackTool::shortInt($remaining);
        $topics = [];
        while ($remaining) {
            $topic = UnPackTool::string($remaining);
            $topics[] = $topic;
        }

        return ['type' => Types::UNSUBSCRIBE, 'message_id' => $messageId, 'topics' => $topics];
    }
}
