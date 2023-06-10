<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Server\Coroutine\Http\Factory;

use ESD\Core\DI\Factory;
use ESD\Server\Coroutine\Http\SwooleResponse;

/**
 * Class ResponseFactory
 * @package ESD\Server\Coroutine\Http\Factory
 */
class ResponseFactory implements Factory
{

    public function create(?array $params)
    {
        return new SwooleResponse();
    }
}