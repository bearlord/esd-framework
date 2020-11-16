<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actuator\Aspect;

use ESD\Core\DI\DI;
use ESD\Core\Memory\CrossProcess\Table;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\EasyRoute\Aspect\RouteAspect;
use ESD\Plugins\EasyRoute\Controller\IController;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\RouteConfig;
use ESD\Plugins\EasyRoute\RouteTool\IRoute;
use ESD\Server\Coroutine\Server;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;


class CountAspect extends OrderAspect
{
    use GetLogger;

    /**
     * @var EasyRouteConfig[]
     */
    protected $easyRouteConfigs;
    /**
     * @var IRoute[]
     */
    protected $routeTools = [];

    /**
     * @var IController[]
     */
    protected $controllers = [];

    /**
     * @var RouteConfig
     */
    protected $routeConfig;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Table
     */
    protected $statusTable;

    /**
     * CountAspect constructor.
     */
    public function __construct()
    {
        $this->atBefore(RouteAspect::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "ActuatorCountAspect";
    }


    /**
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     * @param MethodInvocation $invocation
     * @return mixed|void
     * @throws \Exception
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        /**
         * @var $response Response
         * @var $request Request
         */
        list($request, $response) = $invocation->getArguments();
        $path = $request->getUri()->getPath();
        $this->table = DI::getInstance()->get('RouteCountTable');
        $this->table->incr($path,'num_60');
        $this->table->incr($path,'num_3600');
        $this->table->incr($path,'num_86400');
        return $invocation->proceed();
    }

}