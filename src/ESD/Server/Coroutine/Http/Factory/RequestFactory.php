<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Server\Coroutine\Http\Factory;

use ESD\Core\DI\Factory;
use ESD\Server\Coroutine\Http\SwooleRequest;

/**
 * Class RequestFactory
 * @package ESD\Server\Coroutine\Http\Factory
 */
class RequestFactory implements Factory
{

    public function create(?array $params)
    {
        return new SwooleRequest();
    }
}