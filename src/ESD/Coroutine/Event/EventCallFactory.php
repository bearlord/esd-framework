<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Event;

use ESD\Core\DI\Factory;

/**
 * Class EventCallFactory
 * @package ESD\Coroutine\Event
 */
class EventCallFactory implements Factory
{
    /**
     * @param $params
     * @return EventCallImpl|mixed
     */
    public function create(?array $params)
    {
        return new EventCallImpl($params[0], $params[1], $params[2] ?? false);
    }
}