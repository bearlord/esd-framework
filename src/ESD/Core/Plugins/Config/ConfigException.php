<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Exception;

/**
 * Class ConfigException
 * @package ESD\Core\Plugins\Config
 */
class ConfigException extends Exception
{
    /**
     * @param $object
     * @param $field
     * @param $value
     * @throws ConfigException
     */
    public static function AssertNull($object, $field, $value)
    {
        if ($value === null) {
            $name = get_class($object);
            throw new ConfigException("[{$name}] {$field} cannot be empty");
        }
    }
}