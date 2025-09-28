<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Protocol;

use ESD\Plugins\MQTT\Exception\InvalidArgumentException;
use ESD\Plugins\MQTT\Packet\PackV5;
use ESD\Plugins\MQTT\Packet\UnPackV5;
use ESD\Plugins\MQTT\Tools\PackTool;
use ESD\Plugins\MQTT\Tools\UnPackTool;


class ProtocolV5 implements ProtocolInterface
{
    /**
     * @param array $array
     * @return string
     */
    public static function pack(array $array): string
    {
        $type = $array['type'];
        switch ($type) {
            case Types::CONNECT:
                $package = PackV5::connect($array);
                break;

            case Types::CONNACK:
                $package = PackV5::connAck($array);
                break;

            case Types::PUBLISH:
                $package = PackV5::publish($array);
                break;

            case Types::PUBACK:
            case Types::PUBREC:
            case Types::PUBREL:
            case Types::PUBCOMP:
                $package = PackV5::genReasonPhrase($array);
                break;

            case Types::SUBSCRIBE:
                $package = PackV5::subscribe($array);
                break;

            case Types::SUBACK:
                $package = PackV5::subAck($array);
                break;

            case Types::UNSUBSCRIBE:
                $package = PackV5::unSubscribe($array);
                break;

            case Types::UNSUBACK:
                $package = PackV5::unSubAck($array);
                break;

            case Types::PINGREQ:
            case Types::PINGRESP:
                $package = PackTool::packHeader($type, 0);
                break;

            case Types::DISCONNECT:
                $package = PackV5::disconnect($array);
                break;

            case Types::AUTH:
                $package = PackV5::auth($array);
                break;

            default:
                throw new InvalidArgumentException('MQTT Type not exist');
        }

        return $package;
    }

    /**
     * @param string $data
     * @return array
     */
    public static function unpack(string $data): array
    {
        $type = UnPackTool::getType($data);
        $remaining = UnPackTool::getRemaining($data);
        switch ($type) {
            case Types::CONNECT:
                $package = UnPackV5::connect($remaining);
                break;

            case Types::CONNACK:
                $package = UnPackV5::connAck($remaining);
                break;

            case Types::PUBLISH:
                $dup = ord($data[0]) >> 3 & 0x1;
                $qos = ord($data[0]) >> 1 & 0x3;
                $retain = ord($data[0]) & 0x1;
                $package = UnPackV5::publish($dup, $qos, $retain, $remaining);
                break;

            case Types::PUBACK:
            case Types::PUBREC:
            case Types::PUBREL:
            case Types::PUBCOMP:
                $package = UnPackV5::getReasonCode($type, $remaining);
                break;

            case Types::PINGREQ:
            case Types::PINGRESP:
                $package = ['type' => $type];
                break;

            case Types::DISCONNECT:
                $package = UnPackV5::disconnect($remaining);
                break;

            case Types::SUBSCRIBE:
                $package = UnPackV5::subscribe($remaining);
                break;

            case Types::SUBACK:
                $package = UnPackV5::subAck($remaining);
                break;

            case Types::UNSUBSCRIBE:
                $package = UnPackV5::unSubscribe($remaining);
                break;

            case Types::UNSUBACK:
                $package = UnPackV5::unSubAck($remaining);
                break;

            case Types::AUTH:
                $package = UnPackV5::auth($remaining);
                break;

            default:
                $package = [];
        }

        return $package;
    }
}
