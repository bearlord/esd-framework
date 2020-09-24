<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Server\Co\Http\Factory;

use ESD\Core\DI\Factory;
use ESD\Server\Co\Http\SwooleRequest;

/**
 * Class RequestFactory
 * @package ESD\Server\Co\Http\Factory
 */
class RequestFactory implements Factory
{

    public function create($params)
    {
        return new SwooleRequest();
    }
}