<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Saber\Interceptors;

use Swlib\Saber\Request;

/**
 * Class BeforeInterceptor
 * @package ESD\Plugins\Saber\Interceptors
 */
abstract class BeforeInterceptor extends Interceptor
{
    /**
     * BeforeInterceptor constructor.
     */
    public function __construct()
    {
        parent::__construct(Interceptor::BEFORE);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    abstract public function handle(Request $request);
}