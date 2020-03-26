<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Logger;


use ESD\Core\Exception;

class Logger extends \Monolog\Logger
{
    public function addRecord($level, $message, array $context = array())
    {
        if ($message instanceof Exception) {
            if (!$message->isTrace()) {
                return parent::addRecord(\Monolog\Logger::DEBUG, $message, $context);
            }
        }
        return parent::addRecord($level, $message, $context);
    }
}