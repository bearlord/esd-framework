<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

use ESD\Core\Server\Server;

/**
 * Debug class
 */
class Debug
{

    const NONE = 0;
    const ERR = 1;
    const WARN = 2;
    const INFO = 3;
    const NOTICE = 4;
    const DEBUG = 5;
    const ALL = 15;

    /**
     * Debug flag
     *
     * Disabled by default.
     *
     * @var bool
     */
    protected static $enabled = false;

    /**
     * Enable Debug
     */
    public static function enable()
    {
        self::$enabled = true;
    }

    /**
     * Disable Debug
     */
    public static function disable()
    {
        self::$enabled = false;
    }

    /**
     * Current Log Priority
     *
     * @var int
     */
    protected static $priority = self::WARN;

    /**
     * Log Priority
     *
     * @param int $priority
     */
    public static function setLogPriority($priority)
    {
        self::$priority = (int)$priority;
    }

    /**
     * Log Message
     *
     * Message will be logged using error_log(), configure it with ini_set('error_log', ??)
     * If debug is enabled, Message will also be sent to stdout.
     *
     * @param int $priority
     * @param string $message
     * @param string $bin_dump If $bin_dump is not empty, hex/ascii char will be dumped
     */
    public static function log($priority, $message, $bin_dump = '')
    {
        if ($bin_dump) {
            $bin_dump = Utility::printHex($bin_dump, true, 16, true);
            $message .= "\n" . $bin_dump;
        }

        if ($priority <= self::$priority) {
            if (Server::$instance == null) {
                printf("%s\n", $message);
            } else {
                Server::$instance->getLog()->warning($message);
            }
        } else {
            if (self::$enabled) {
                if (Server::$instance == null) {
                    print_r($message . "\n");
                } else {
                    Server::$instance->getLog()->debug($message);
                }
            }
        }
    }
}