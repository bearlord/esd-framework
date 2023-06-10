<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Channel;

use ESD\Core\DI\Factory;

/**
 * Class ChannelFactory
 * @package ESD\Coroutine\Channel
 */
class ChannelFactory implements Factory
{
    /**
     * @param array $params
     * @return ChannelImpl|mixed
     */
    public function create(?array $params)
    {
        return new ChannelImpl($params[0] ?? 1);
    }
}