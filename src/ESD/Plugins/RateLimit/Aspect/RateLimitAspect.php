<?php

namespace ESD\Plugins\RateLimit\Aspect;

use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\Response;
use ESD\Coroutine\Coroutine;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Helpers\Json;
use ESD\Goaop\Aop\Intercept\MethodInvocation;
use ESD\Goaop\Lang\Annotation\After;
use ESD\Goaop\Lang\Annotation\Around;
use ESD\Goaop\Lang\Annotation\Before;
use ESD\Nikic\FastRoute\Dispatcher;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Pack\GetBoostSend;
use ESD\Plugins\RateLimit\Annotation\RateLimit;
use ESD\Plugins\RateLimit\Exception\RateLimitException;
use ESD\Plugins\RateLimit\Handle\RateLimitHandler;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\TokenBucket\Storage\StorageException;

class RateLimitAspect extends OrderAspect
{

    /**
     * @var array
     */
    private array $config;

    /**
     * @var RateLimitHandler
     */
    private RateLimitHandler $rateLimitHandler;


    public function __construct()
    {
        $this->rateLimitHandler = new RateLimitHandler();

    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'RateLimit';
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
        /** @var ClientData $clientData */
        $clientData = getContextValueByClassName(ClientData::class);
        //Port
        $port = $clientData->getClientInfo()->getServerPort();
        //Request method
        $requestMethod = strtoupper($clientData->getRequestMethod());

        //Route info
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch(sprintf("%s:%s", $port, $requestMethod), $clientData->getPath());

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $clientData->setControllerName($handler[0]->name);
                $clientData->setMethodName($handler[1]->name);
                $methodReflection = $handler[1]->getReflectionMethod();
                $annotations = EasyRoutePlugin::$instance->getScanClass()->getMethodAndInterfaceAnnotations($methodReflection);
                $clientData->setAnnotations($annotations);


                foreach ($annotations as $annotation) {
                    switch (true) {
                        case ($annotation instanceof RateLimit):
                            $bucketKey = $annotation->key;
                            
                            if (is_array($bucketKey) && count($bucketKey) == 2) {
                                $bucketKey = call_user_func([$bucketKey[0], $bucketKey[1]], $invocation);
                            }

                            if (!$bucketKey) {
                                $bucketKey = $clientData->getPath();
                            }

                            $bucket = $this->rateLimitHandler->build($bucketKey, $annotation->create, $annotation->capacity, $annotation->waitTimeout);

                            $maxTime = microtime(true) + $annotation->waitTimeout;
                            $seconds = 0;

                            while (true) {
                                try {
                                    if ($bucket->consume($annotation->consume, $seconds)) {
                                        return $invocation->proceed();
                                    }
                                } catch (StorageException $exception) {
                                    throw $exception;
                                }

                                if (microtime(true) + $seconds > $maxTime) {
                                    break;
                                }
                                Coroutine::sleep(max($seconds, 0.001));
                            }

                            if (empty($annotation->limitCallback)
                                || !is_array($annotation->limitCallback)
                                || count($annotation->limitCallback) != 2 ) {
                                throw new RateLimitException('Service Unavailable.', 503);
                            }

                            $callResult = call_user_func([$annotation->limitCallback[0], $annotation->limitCallback[1]], $seconds);

                            $clientData = getContextValueByClassName(ClientData::class);

                            $clientData->getResponse()->withHeader("Content-Type", "application/json");
                            $clientData->getResponse()->withContent(Json::encode($callResult))->end();

                            break;

                        default:
                            return $invocation->proceed();
                    }
                }
                break;
        }
    }


}