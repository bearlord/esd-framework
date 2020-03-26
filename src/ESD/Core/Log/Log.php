<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Log;


use Psr\Log\LoggerInterface;

class Log implements LoggerInterface
{

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message . "\n");
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if($message instanceof \Throwable){
            $message = $message->getMessage();
        }
        print_r($message."\n");
    }
}