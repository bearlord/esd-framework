<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Logger;

use ESD\Core\Exception;

/**
 * Class Logger
 * @package ESD\Core\Plugins\Logger
 */
class Logger extends \Monolog\Logger
{
    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return mixed
     */
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