<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actuator\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Beans\Request;
use ESD\Plugins\Actuator\ActuatorController;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\EasyRoute\Aspect\RouteAspect;
use ESD\Nikic\FastRoute\Dispatcher;
use ESD\Goaop\Go\Aop\Intercept\MethodInvocation;
use ESD\Goaop\Go\Lang\Annotation\Around;

class ActuatorAspect extends OrderAspect
{
    use GetLogger;

    /**
     * @var ActuatorController
     */
    private $actuatorController;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * ActuatorAspect constructor.
     * @param ActuatorController $actuatorController
     * @param Dispatcher $dispatcher
     */
    public function __construct(ActuatorController $actuatorController, Dispatcher $dispatcher)
    {
        $this->actuatorController = $actuatorController;
        $this->dispatcher = $dispatcher;
        $this->atBefore(RouteAspect::class);
    }

    /**
     * Around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @return mixed|null
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundRequest(MethodInvocation $invocation)
    {
        list($request, $response) = $invocation->getArguments();
        $routeInfo = $this->dispatcher->dispatch($request->getServer(Request::SERVER_REQUEST_METHOD), $request->getServer(Request::SERVER_REQUEST_URI));

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return $invocation->proceed();

            case Dispatcher::METHOD_NOT_ALLOWED:
                $response->withStatus(405);
                $response->withHeader("Content-Type", "text/html; charset=utf-8");
                $response->withContent("不支持的请求方法");
                return null;

            case Dispatcher::FOUND: // 找到对应的方法
                $className = $routeInfo[1];
                $vars = $routeInfo[2]; // 获取请求参数
                $response->withHeader("Content-Type", "application/json; charset=utf-8");
                $response->withContent(call_user_func([$this->actuatorController, $className], $vars));
                return null;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "ActuatorAspect";
    }
}