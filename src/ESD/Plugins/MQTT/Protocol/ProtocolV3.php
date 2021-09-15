<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Protocol;

use ESD\Plugins\MQTT\Exception\InvalidArgumentException;
use ESD\Plugins\MQTT\Exception\RuntimeException;
use ESD\Plugins\MQTT\Packet\PackV3;
use ESD\Plugins\MQTT\Packet\UnPackV3;
use ESD\Plugins\MQTT\Tools\PackTool;
use ESD\Plugins\MQTT\Tools\UnPackTool;
use Throwable;
use TypeError;

/**
 * Class ProtocolV3
 * @package ESD\Plugins\MQTT\Protocol
 */
class ProtocolV3 implements ProtocolInterface
{
    /**
     * @param array $array
     * @return string
     * @throws Throwable
     */
    public static function pack(array $array): string
    {
        try {
            $type = $array['type'];
            switch ($type) {
                case Types::CONNECT:
                    $package = PackV3::connect($array);
                    break;

                case Types::CONNACK:
                    $package = PackV3::connAck($array);
                    break;

                case Types::PUBLISH:
                    $package = PackV3::publish($array);
                    break;

                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $body = PackTool::shortInt($array['message_id']);
                    if ($type === Types::PUBREL) {
                        $head = PackTool::packHeader($type, strlen($body), 0, 1);
                    } else {
                        $head = PackTool::packHeader($type, strlen($body));
                    }
                    $package = $head . $body;
                    break;

                case Types::SUBSCRIBE:
                    $package = PackV3::subscribe($array);
                    break;

                case Types::SUBACK:
                    $package = PackV3::subAck($array);
                    break;

                case Types::UNSUBSCRIBE:
                    $package = PackV3::unSubscribe($array);
                    break;

                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = PackTool::packHeader($type, 0);
                    break;

                default:
                    throw new InvalidArgumentException('MQTT Type not exist');
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw $e;
        }

        return $package;
    }

    /**
     * @param string $data
     * @return array
     * @throws Throwable
     */
    public static function unpack(string $data): array
    {
        try {
            $type = UnPackTool::getType($data);
            $remaining = UnPackTool::getRemaining($data);
            switch ($type) {
                case Types::CONNECT:
                    $package = UnPackV3::connect($remaining);
                    break;

                case Types::CONNACK:
                    $package = UnPackV3::connAck($remaining);
                    break;

                case Types::PUBLISH:
                    $dup = ord($data[0]) >> 3 & 0x1;
                    $qos = ord($data[0]) >> 1 & 0x3;
                    $retain = ord($data[0]) & 0x1;
                    $package = UnPackV3::publish($dup, $qos, $retain, $remaining);
                    break;

                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $package = ['type' => $type, 'message_id' => UnPackTool::shortInt($remaining)];
                    break;

                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = ['type' => $type];
                    break;

                case Types::SUBSCRIBE:
                    $package = UnPackV3::subscribe($remaining);
                    break;

                case Types::SUBACK:
                    $package = UnPackV3::subAck($remaining);
                    break;

                case Types::UNSUBSCRIBE:
                    $package = UnPackV3::unSubscribe($remaining);
                    break;

                default:
                    $package = [];
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw $e;
        }

        return $package;
    }
}
