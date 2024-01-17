<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Tools;

class PackTool extends Common
{
    /**
     * @param string $str
     * @return string
     */
    public static function string(string $str): string
    {
        $len = strlen($str);

        return pack('n', $len) . $str;
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    public static function stringPair(string $key, string $value): string
    {
        return static::string($key) . static::string($value);
    }

    /**
     * @param int $int
     * @return string
     */
    public static function longInt(int $int): string
    {
        return pack('N', $int);
    }

    /**
     * @param int $int
     * @return string
     */
    public static function shortInt(int $int): string
    {
        return pack('n', $int);
    }

    /**
     * @param int $int
     * @return string
     */
    public static function varInt(int $int): string
    {
        return static::packRemainingLength($int);
    }

    /**
     * @param int $type
     * @param int $bodyLength
     * @param int $dup
     * @param int $qos
     * @param int $retain
     * @return string
     */
    public static function packHeader(int $type, int $bodyLength, int $dup = 0, int $qos = 0, int $retain = 0): string
    {
        $type = $type << 4;
        if ($dup) {
            $type |= 1 << 3;
        }
        if ($qos) {
            $type |= $qos << 1;
        }
        if ($retain) {
            $type |= 1;
        }

        return chr($type) . static::packRemainingLength($bodyLength);
    }

    /**
     * @param int $bodyLength
     * @return string
     */
    private static function packRemainingLength(int $bodyLength): string
    {
        $string = '';
        do {
            $digit = $bodyLength % 128;
            $bodyLength = $bodyLength >> 7;
            if ($bodyLength > 0) {
                $digit = ($digit | 0x80);
            }
            $string .= chr($digit);
        } while ($bodyLength > 0);

        return $string;
    }
}
