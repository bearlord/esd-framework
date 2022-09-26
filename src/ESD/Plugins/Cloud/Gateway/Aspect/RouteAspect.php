<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Aspect;

use ESD\Core\Exception;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Cloud\Gateway\Controller\IController;
use ESD\Plugins\Cloud\Gateway\Filter\AbstractFilter;
use ESD\Plugins\Cloud\Gateway\Filter\FilterManager;
use ESD\Plugins\Cloud\Gateway\GatewayConfig;
use ESD\Plugins\Cloud\Gateway\RouteConfig;
use ESD\Plugins\Cloud\Gateway\RouteException;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Pack\Aspect\PackAspect;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use FastRoute\Dispatcher;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Swlib\Saber;
use Swoole\Coroutine\Channel;

/**
 * Class RouteAspect
 * @package ESD\Plugins\Cloud\Gateway\Aspect
 */
class RouteAspect extends OrderAspect
{
    use GetLogger;
    use GetBoostSend;

    /**
     * @var GatewayConfig[]
     */
    protected $gatewayConfigs;
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
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * RouteAspect constructor.
     * @param $gatewayConfigs
     * @param RouteConfig $routeConfig
     * @throws \Exception
     */
    public function __construct($gatewayConfigs, RouteConfig $routeConfig)
    {
        $this->gatewayConfigs = $gatewayConfigs;

        /** @var GatewayConfig $gatewayConfig */
        foreach ($this->gatewayConfigs as $gatewayConfig) {
            $className = $gatewayConfig->getRouteTool();
            if (!isset($this->routeTools[$className])) {
                $this->routeTools[$className] = DIget($className);
            }
        }

        $this->routeConfig = $routeConfig;
        $this->filterManager = DIGet(FilterManager::class);
        $this->atAfter(PackAspect::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "RouteAspect";
    }

    /**
     * Around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundHttpRequest(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $gatewayConfig = $this->gatewayConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("GatewayConfig", $gatewayConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);

        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }

        if ($upsteam = $this->upsteam($clientData)) {
            $clientData->getResponse()->withHeaders($upsteam[0]);
            $clientData->getResponse()->append($upsteam[1]);
            return;
        }

        $routeTool = $this->routeTools[$gatewayConfig->getRouteTool()];

        try {
            if (!$routeTool->handleClientData($clientData, $gatewayConfig)) {
                return;
            }
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $clientData->setResponseRaw($controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams()));

            if ($this->filterManager->filter(AbstractFilter::FILTER_ROUTE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
                return;
            }

            $clientData->getResponse()->append($clientData->getResponseRaw());
            $this->filterManager->filter(AbstractFilter::FILTER_PRO, $clientData);
        } catch (\Throwable $e) {
            //The errors here will be handed over to the IndexController
            $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
            $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());

            $result = $controllerInstance->onExceptionHandle($e);
            if (!empty($result)) {
                $clientData->getResponse()->append($result);
            }
            throw $e;
        }
        return;
    }

    /**
     * Get controller
     *
     * @param $controllerName
     * @return IController
     * @throws RouteException
     */
    private function getController($controllerName)
    {
        if (!isset($this->controllers[$controllerName])) {
            if (class_exists($controllerName)) {
                $controller = DIget($controllerName);
                if ($controller instanceof IController) {
                    $this->controllers[$controllerName] = $controller;
                    return $controller;
                } else {
                    throw new RouteException(sprintf("Class %s should extend IController", $controllerName));
                }
            } else {
                throw new RouteException(sprintf("%s Not found", $controllerName));
            }
        } else {
            return $this->controllers[$controllerName];
        }
    }

    /**
     * After onTcpConnect
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpConnect(*))")
     */
    protected function afterTcpConnect(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();

        $clientInfo = Server::$instance->getClientInfo($fd);
        //Server port
        $serverPort = $clientInfo->getServerPort();
        //Request method
        $requestMethod = 'TCP';
        //Defined path
        $onConnectPath = '/onConnect';
        //Route info
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch(sprintf("%s:%s", $serverPort, $requestMethod), $onConnectPath);

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            return false;
        }

        try {
            $instance = new $routeInfo[1][0]->name();
            call_user_func_array([$instance, $routeInfo[1][1]->name], [$fd, $reactorId]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpReceive(*))")
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $gatewayConfig = $this->gatewayConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("GatewayConfig", $gatewayConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$gatewayConfig->getRouteTool()];

        try {
            if (!$routeTool->handleClientData($clientData, $gatewayConfig)) {
                return;
            }
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $clientData->setResponseRaw($controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams()));
            if ($this->filterManager->filter(AbstractFilter::FILTER_ROUTE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
                return;
            }
            if ($gatewayConfig->getAutoSendReturnValue()) {
                $this->autoBoostSend($clientData->getFd(), $clientData->getResponseRaw());
            }
        } catch (\Throwable $e) {
            try {
                //The errors here will be handed over to the IndexController
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * After onTcpClose
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpClose(*))")
     */
    protected function afterTcpClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();

        $clientInfo = Server::$instance->getClientInfo($fd);
        //Server port
        $serverPort = $clientInfo->getServerPort();
        //Request method
        $requestMethod = 'TCP';
        //Defined path
        $onClosePath = '/onClose';
        //Route info
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch(sprintf("%s:%s", $serverPort, $requestMethod), $onClosePath);

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            return false;
        }

        try {
            $instance = new $routeInfo[1][0]->name();
            call_user_func_array([$instance, $routeInfo[1][1]->name], [$fd, $reactorId]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * After onWsOpen
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsOpen(*))")
     */
    protected function afterWsOpen(MethodInvocation $invocation)
    {
        $request = $invocation->getArguments()[0];
        //fd
        $fd = $request->getFd();
        //Client Info
        $clientInfo = Server::$instance->getClientInfo($fd);
        //ReactorId
        $reactorId = $clientInfo->getReactorId();
        //Server port
        $serverPort = $clientInfo->getServerPort();
        //Request method
        $requestMethod = 'WS';
        //Defined path
        $onConnectPath = '/onWsOpen';
        //Route info
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch(sprintf("%s:%s", $serverPort, $requestMethod), $onConnectPath);

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            return false;
        }

        try {
            $instance = new $routeInfo[1][0]->name();
            call_user_func_array([$instance, $routeInfo[1][1]->name], [$fd, $reactorId]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsMessage(*))")
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $gatewayConfig = $this->gatewayConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("GatewayConfig", $gatewayConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$gatewayConfig->getRouteTool()];
        try {
            if (!$routeTool->handleClientData($clientData, $gatewayConfig)) {
                return;
            }
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $clientData->setResponseRaw($controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams()));

            if ($this->filterManager->filter(AbstractFilter::FILTER_ROUTE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
                return;
            }
            $this->autoBoostSend($clientData->getFd(), $clientData->getResponseRaw());
        } catch (\Throwable $e) {
            try {
                //The errors here will be handed over to the IndexController
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
            throw $e;
        }
        return;
    }

    /**
     * After onWsClose
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsClose(*))")
     */
    protected function afterWSClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();

        $clientInfo = Server::$instance->getClientInfo($fd);
        //Server port
        $serverPort = $clientInfo->getServerPort();
        //Request method
        $requestMethod = 'WS';
        //Define path
        $onClosePath = '/onWsClose';
        //Route info
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch(sprintf("%s:%s", $serverPort, $requestMethod), $onClosePath);

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            return false;
        }

        try {
            $instance = new $routeInfo[1][0]->name();
            call_user_func_array([$instance, $routeInfo[1][1]->name], [$fd, $reactorId]);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws \Throwable
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $gatewayConfig = $this->gatewayConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("GatewayConfig", $gatewayConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$gatewayConfig->getRouteTool()];
        try {
            if (!$routeTool->handleClientData($clientData, $gatewayConfig)) {
                return;
            }
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams());
        } catch (\Throwable $e) {
            try {
                //The errors here will be handed over to the ErrorController
                $controllerInstance = $this->getController($this->routeConfig->getErrorControllerName());
                $controllerInstance->initialization($routeTool->getControllerName(), $routeTool->getMethodName());
                $controllerInstance->onExceptionHandle($e);
            } catch (\Throwable $e) {
                $this->warn($e);
            }
        }
        return;
    }

    /**
     * @param ClientData $clientData
     * @return void
     */
    protected function upsteam($clientData)
    {
        $staticNodeRoutes = [
            [
                'node' => [
                    'scheme' => 'http',
                    'host' => 'localhost',
                    'port' => 8080
                ],
                'routes' => [
                    [
                        'method' => 'get',
                        'uri_prefix' => '/customer'
                    ],
                    [
                        'method' => 'get',
                        'uri_prefix' => '/hello'
                    ],
                ]
            ]
        ];

        $serverParams = $clientData->getRequest()->getServerParams();

        $uri = $clientData->getRequest()->getUri()->getPath();
        $query = $clientData->getRequest()->getUri()->getQuery();
        $method = $clientData->getRequest()->getMethod();

        $findNode = $this->findNode($uri, $method, $staticNodeRoutes);
        if (!$findNode) {
            return false;
        }

        $nodeUrl = sprintf("%s://%s:%s/%s",
            $findNode['node']['scheme'],
            $findNode['node']['host'],
            $findNode['node']['port'],
            ltrim($uri, "/"));
        if ($query) {
            $nodeUrl .= "?". $query;
        }

        $channel = new Channel(1);
        goWithContext(function () use ($channel, $nodeUrl) {
            $saber = Saber::create();
            $handle = $saber->get($nodeUrl);
            $responeHeader = $handle->getHeaders();
            $responeData = $handle->getBody()->getContents();

            $channel->push([$responeHeader, $responeData]);
        });
        $response = $channel->pop();
        return $response;
    }

    protected function findNode($uri, $method, $nodes)
    {
        $findNode = null;
        foreach ($nodes as $key => $node) {
            $findRoute = $this->findRouteInNode($uri, $method, $node);
            if ($findRoute) {
                $findNode['node'] = $node['node'];
                $findNode['route'] = $findRoute;
                break;
            }
        }
        return $findNode;
    }

    protected function findRouteInNode($uri, $method, $node)
    {
        $findRoute = null;
        foreach ($node['routes'] as $route) {
            if (strtolower($route['method']) == strtolower($method)) {
                if (preg_match("@".$route['uri_prefix']."@", $uri)) {
                    $findRoute = $route;
                    break;
                }
            }
        }
        return $findRoute;
    }
}
