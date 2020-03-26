<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Logger;

use ESD\Core\Server\Server;
use Monolog\Logger;

/**
 * Trait GetLogger
 * @package ESD\Core\Plugins\Logger
 */
trait GetLogger
{
    /**
     * @param $level
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        Server::$instance->getLog()->log($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function debug($message, array $context = array())
    {
        $this->addRecord(Logger::DEBUG, $message, $context);
    }

    /**
     * Adds a log record.
     *
     * @param int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function addRecord($level, $message, array $context = array())
    {
        Server::$instance->getLog()->log($level, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function info($message, array $context = array())
    {
        $this->addRecord(Logger::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function notice($message, array $context = array())
    {
        $this->addRecord(Logger::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function warn($message, array $context = array())
    {
        $this->addRecord(Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function warning($message, array $context = array())
    {
        $this->addRecord(Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function err($message, array $context = array())
    {
        $this->addRecord(Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function error($message, array $context = array())
    {
        $this->addRecord(Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function crit($message, array $context = array())
    {
        $this->addRecord(Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function critical($message, array $context = array())
    {
        $this->addRecord(Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function alert($message, array $context = array())
    {
        $this->addRecord(Logger::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function emerg($message, array $context = array())
    {
        $this->addRecord(Logger::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param string $message The log message
     * @param array $context The log context
     * @return void Whether the record has been processed
     * @throws \Exception
     */
    public function emergency($message, array $context = array())
    {
        $this->addRecord(Logger::EMERGENCY, $message, $context);
    }
}