<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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