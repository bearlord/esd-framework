<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Whoops\Aspect;

use ESD\Core\Server\Beans\Response;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Whoops\WhoopsConfig;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use Whoops\Run;

/**
 * Class WhoopsAspect
 * @package ESD\Plugins\Whoops\Aspect
 */
class WhoopsAspect extends OrderAspect
{
    /**
     * @var Run
     */
    private $run;

    /**
     * @var WhoopsConfig
     */
    protected $whoopsConfig;

    public function __construct(Run $run, WhoopsConfig $whoopsConfig)
    {
        $this->run = $run;
        $this->whoopsConfig = $whoopsConfig;
    }

    /**
     * Around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     * @return mixed|null
     * @throws \Throwable
     */
    protected function aroundRequest(MethodInvocation $invocation)
    {
        /**
         * @var $response Response
         */
        list($request, $response) = $invocation->getArguments();
        $result = null;
        try {
            $result = $invocation->proceed();
        } catch (\Throwable $e) {
            setContextValue("lastException", $e);
        }
        $e = getContextValue("lastException");
        if ($e != null && $this->whoopsConfig->isEnable() && Server::$instance->getServerConfig()->isDebug()) {
            $response->withContent($this->run->handleException($e));
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "WhoopsAspect";
    }
}