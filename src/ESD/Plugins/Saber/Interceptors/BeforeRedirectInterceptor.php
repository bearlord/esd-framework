<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Request;
use Swlib\Saber\Response;

/**
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class BeforeRedirectInterceptor extends Interceptor
{
    /**
     * BeforeRedirectInterceptor constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(Interceptor::BEFORE_REDIRECT);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    abstract public function handle(Request $request,Response $response);
}