<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp\Message;

/**
 * Class Type
 * @package ESD\Plugins\Amqp\Message
 */
class Type
{
    const DIRECT = 'direct';

    const FANOUT = 'fanout';

    const TOPIC = 'topic';

    /**
     * @return string[]
     */
    public static function items()
    {
        return [
            self::DIRECT,
            self::FANOUT,
            self::TOPIC,
        ];
    }
}