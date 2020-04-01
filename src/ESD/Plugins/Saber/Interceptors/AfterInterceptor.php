<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Response;

/**
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class AfterInterceptor extends Interceptor
{
    /**
     * AfterInterceptor constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(Interceptor::AFTER);
    }

    /**
     * @param Response $response
     * @return mixed
     */
    abstract public function handle(Response $response);
}