<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Log;

use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package ESD\Core\Log
 */
class Log implements LoggerInterface
{

    /**
     * System is unusable.
     *
     * @param $message
     * @param array|null $context
     *
     * @return void
     */
    public function emergency($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param $message
     * @param array|null $context
     *
     * @return void
     */
    public function alert($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param $message
     * @param array|null $context
     *
     * @return void
     */
    public function critical($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function error($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function warning($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Normal but significant events.
     *
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function notice($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function info($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Detailed debug information.
     *
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function debug($message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param mixed $message
     * @param array|null $context
     *
     * @return void
     */
    public function log($level, $message, ?array $context = [])
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        printf("%s\n", $message);
    }
}
