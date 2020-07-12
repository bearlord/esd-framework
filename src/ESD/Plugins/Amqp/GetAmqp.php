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
        /**
         * @var $db AMQPChannel
         */
        $db = getContextValue("AmqpChannel:$name");
        if ($db == null || !$db->is_open()) {
            $amqpPool = getDeepContextValueByClassName(AmqpPool::class);
            if ($amqpPool instanceof AmqpPool) {
                $db = $amqpPool->channel($name);
                setContextValue("AmqpChannel:$name", $db);
                return $db;
            } else {
                throw new AmqpException("没有找到名为{$name}的amqp连接");
            }
        } else {
            return $db;
        }
    }
}