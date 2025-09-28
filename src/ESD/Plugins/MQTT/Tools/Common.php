<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Tools;

/**
 * @method static string hexDump(string $encode)
 * @method static string hexDumpAscii(string $encode)
 * @method static string printableText(string $encode)
 * @method static string hexStream(string $encode)
 * @method static string ascii(string $encode)
 */
abstract class Common
{
    /**
     * @param string $data
     * @return void
     */
    public static function printf(string $data)
    {
        echo "\033[36m";
        for ($i = 0; $i < strlen($data); $i++) {
            $ascii = ord($data[$i]);
            if ($ascii > 31) {
                $chr = $data[$i];
            } else {
                $chr = ' ';
            }
            printf("%4d: %08b : 0x%02x : %d : %s\n", $i, $ascii, $ascii, $ascii, $chr);
        }
        echo "\033[0m";
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return (new Debug())->setEncode(...$arguments)->{$method}();
    }
}
