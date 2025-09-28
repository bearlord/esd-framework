<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Tools;

use ESD\Plugins\MQTT\Exception\InvalidArgumentException;
use ESD\Plugins\MQTT\Exception\LengthException;
use ESD\Plugins\MQTT\Protocol\Types;

class UnPackTool extends Common
{
    /**
     * @param string $data
     * @return int
     */
    public static function getType(string $data): int
    {
        return ord($data[0]) >> 4;
    }

    /**
     * @param string $remaining
     * @return string
     */
    public static function string(string &$remaining): string
    {
        $length = unpack('n', $remaining)[1];
        if ($length + 2 > strlen($remaining)) {
            throw new LengthException("unpack remaining length error, get {$length}");
        }
        $string = substr($remaining, 2, $length);
        $remaining = substr($remaining, $length + 2);

        return $string;
    }

    /**
     * @param string $remaining
     * @return int
     */
    public static function shortInt(string &$remaining): int
    {
        $tmp = unpack('n', $remaining);
        $remaining = substr($remaining, 2);

        return $tmp[1];
    }

    /**
     * @param string $remaining
     * @return int
     */
    public static function longInt(string &$remaining): int
    {
        $tmp = unpack('N', $remaining);
        $remaining = substr($remaining, 4);

        return $tmp[1];
    }

    /**
     * @param string $remaining
     * @return int
     */
    public static function byte(string &$remaining): int
    {
        $tmp = ord($remaining[0]);
        $remaining = substr($remaining, 1);

        return $tmp;
    }

    /**
     * @param string $remaining
     * @param int|null $len
     * @return int
     */
    public static function varInt(string &$remaining, ?int &$len): int
    {
        $remainingLength = static::getRemainingLength($remaining, $headBytes);
        $len = $headBytes;

        $result = $shift = 0;
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($remaining[$i]);
            $result |= ($byte & 0x7F) << $shift++ * 7;
        }

        $remaining = substr($remaining, $headBytes, $remainingLength);

        return $result;
    }

    /**
     * @param string $data
     * @param int|null $headBytes
     * @return int
     */
    protected static function getRemainingLength(string $data, ?int &$headBytes): int
    {
        $headBytes = $multiplier = 1;
        $value = 0;
        do {
            if (!isset($data[$headBytes])) {
                throw new LengthException('Malformed Remaining Length');
            }
            $digit = ord($data[$headBytes]);
            $value += ($digit & 127) * $multiplier;
            $multiplier *= 128;
            ++$headBytes;
        } while (($digit & 128) != 0);

        return $value;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function getRemaining(string $data): string
    {
        $remainingLength = static::getRemainingLength($data, $headBytes);

        return substr($data, $headBytes, $remainingLength);
    }

    /**
     * Get the MQTT protocol level.
     * @param string $data
     * @return int
     */
    public static function getLevel(string $data): int
    {
        $type = static::getType($data);

        if ($type !== Types::CONNECT) {
            throw new InvalidArgumentException(sprintf('packet must be of type connect, %s given', Types::getType($type)));
        }

        $remaining = static::getRemaining($data);
        $length = unpack('n', $remaining)[1];

        return ord($remaining[$length + 2]);
    }
}
