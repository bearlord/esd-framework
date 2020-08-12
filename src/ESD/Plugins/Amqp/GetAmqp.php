<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Trait GetAmqp
 * @package ESD\Plugins\Amqp
 */
trait GetAmqp
{
    /**
     * @param string $name
     * @return AmqpChannel
     * @throws AmqpException
     * @throws \ReflectionException
     */
    public function amqp(string $name = 'default')
    {
        $poolKey = sprintf("AmqpChannel:%s", $name);
        /**
         * @var $db AMQPChannel
         */
        $db = getContextValue($poolKey);
        if ($db == null || !$db->is_open()) {
            $amqpPool = getDeepContextValueByClassName(AmqpPool::class);
            if ($amqpPool instanceof AmqpPool) {
                $db = $amqpPool->channel($name);
                setContextValue($poolKey, $db);
                return $db;
            } else {
                throw new AmqpException("No Amqp connection pool named {$poolKey} was found");
            }
        } else {
            return $db;
        }
    }
}