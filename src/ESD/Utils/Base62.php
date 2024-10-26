<?php

namespace ESD\Utils;

use InvalidArgumentException;

class Base62
{

    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    private const BASE = 62;

    /**
     * @param int $number
     * @return string
     */
    public static function encode(int $number): string
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Input must be a non-negative integer.');
        }

        if ($number === 0) {
            return self::ALPHABET[0];
        }

        $result = '';
        while ($number > 0) {
            $result = self::ALPHABET[$number % self::BASE] . $result;
            $number = (int)($number / self::BASE);
        }

        return $result;
    }

    /**
     * @param string $string
     * @return int
     */
    public static function decode(string $string): int
    {
        if (empty($string)) {
            throw new InvalidArgumentException('Input string cannot be empty.');
        }

        $result = 0;
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $index = strpos(self::ALPHABET, $string[$i]);
            if ($index === false) {
                throw new InvalidArgumentException('Invalid character "' . $string[$i] . '" found in input string.');
            }

            $result = $result * self::BASE + $index;
        }

        return $result;
    }
}
