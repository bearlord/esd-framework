<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\EasyRoute\Controller\IController;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\Filter\AbstractFilter;
use ESD\Plugins\EasyRoute\Filter\FilterManager;
use ESD\Plugins\EasyRoute\RouteConfig;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\EasyRoute\RouteTool\IRoute;
use ESD\Plugins\Pack\Aspect\PackAspect;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

/**
 * Class RouteAspect
 * @package ESD\Plugins\EasyRoute\Aspect
 */
class RouteAspect extends OrderAspect
{
    use GetLogger;
    use GetBoostSend;

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
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * RouteAspect constructor.
     * @param $easyRouteConfigs
     * @param RouteConfig $routeConfig
     * @throws \Exception
     */
    public function __construct($easyRouteConfigs, RouteConfig $routeConfig)
    {
        $this->easyRouteConfigs = $easyRouteConfigs;
        foreach ($this->easyRouteConfigs as $easyRouteConfig) {
            if (!isset($this->routeTools[$easyRouteConfig->getRouteTool()])) {
                $className = $easyRouteConfig->getRouteTool();
                $this->routeTools[$easyRouteConfig->getRouteTool()] = DIget($className);
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
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];

        try {
            if (!$routeTool->handleClientData($clientData, $easyRouteConfig)) return;
            $controllerInstance = $this->getController($routeTool->getControllerName());
            $clientData->setResponseRaw($controllerInstance->handle($routeTool->getControllerName(), $routeTool->getMethodName(), $routeTool->getParams()));
            if ($this->filterManager->filter(AbstractFilter::FILTER_ROUTE, $clientData) == AbstractFilter::RETURN_END_ROUTE) return;
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
     * Around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpReceive(*))")
     */
    protected function aroundTcpReceive(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];

        try {
            if (!$routeTool->handleClientData($clientData, $easyRouteConfig)) {
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
     * Around onWsMessage
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsMessage(*))")
     */
    protected function aroundWsMessage(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            if (!$routeTool->handleClientData($clientData, $easyRouteConfig)) {
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
     * Around onUdpPacket
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onUdpPacket(*))")
     * @throws \Throwable
     */
    protected function aroundUdpPacket(MethodInvocation $invocation)
    {
        $abstractServerPort = $invocation->getThis();
        $easyRouteConfig = $this->easyRouteConfigs[$abstractServerPort->getPortConfig()->getPort()];
        setContextValue("EasyRouteConfig", $easyRouteConfig);

        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        if ($clientData == null) {
            return;
        }
        if ($this->filterManager->filter(AbstractFilter::FILTER_PRE, $clientData) == AbstractFilter::RETURN_END_ROUTE) {
            return;
        }
        $routeTool = $this->routeTools[$easyRouteConfig->getRouteTool()];
        try {
            if (!$routeTool->handleClientData($clientData, $easyRouteConfig)) {
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
}