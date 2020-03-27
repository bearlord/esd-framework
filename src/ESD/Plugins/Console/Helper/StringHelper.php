<?php

namespace ESD\Plugins\Console\Helper;

/**
 * Class StringHelper
 * @package ESD\Plugins\Console\Helper
 */
class StringHelper
{
    /**
     * Camel case to snake case
     * @param $var
     * @return string
     */
    public static function camel2Snake($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            $str = ord($var[$i]);
            if ($str > 64 && $str < 91) {
                $result .= "_" . strtolower($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }

    /**
     * Snake case to camel case
     *
     * @param $var
     * @return mixed
     */
    public static function snake2Camel($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            if ($var[$i] == "_") {
                $i = $i + 1;
                $result .= strtoupper($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }
}