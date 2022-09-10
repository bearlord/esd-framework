<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\SaberCloud;


use ESD\Core\ParamException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\EasyRoute\Annotation\ModelAttribute;
use ESD\Plugins\EasyRoute\Annotation\PathVariable;
use ESD\Plugins\EasyRoute\Annotation\RequestBody;
use ESD\Plugins\EasyRoute\Annotation\RequestFormData;
use ESD\Plugins\EasyRoute\Annotation\RequestMapping;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;
use ESD\Plugins\EasyRoute\Annotation\RequestRaw;
use ESD\Plugins\EasyRoute\Annotation\RequestRawJson;
use ESD\Plugins\EasyRoute\Annotation\RequestRawXml;
use ESD\Plugins\EasyRoute\Annotation\ResponseBody;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\Cloud\SaberCloud\Annotation\SaberClient;
use ESD\Psr\Cloud\CircuitBreaker;
use FastRoute\RouteParser\Std;
use Swlib\Http\ContentType;
use Swlib\Saber\Response;

class SaberClientProxy
{
    use GetLogger;
    /**
     * @var SaberClient
     */
    private $saberClient;

    /**
     * @var SaberCloud
     */
    private $saberCloud;
    /**
     * @var ScanClass
     */
    private $scanClass;
    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var \ReflectionMethod[]
     */
    private $reflectionMethods;

    /**
     * @var Std
     */
    private $std;

    /**
     * @var array
     */
    private $cache = [];
    /**
     * @var RequestMapping
     */
    private $requestMapping;
    /**
     * @var Response
     */
    private $response;

    /**
     * @var CircuitBreaker
     */
    private $circuitBreaker;

    /**
     * SaberClientProxy constructor.
     * @param \ReflectionClass $reflectionClass
     * @throws SaberCloudException
     */
    public function __construct(\ReflectionClass $reflectionClass)
    {
        $this->reflectionClass = $reflectionClass;
        $this->saberCloud = DIGet(SaberCloud::class);
        $this->std = DIGet(Std::class);
        $this->scanClass = DIGet(ScanClass::class);
        try {
            $this->circuitBreaker = DIGet(CircuitBreaker::class);
        } catch (\Throwable $e) {

        }
        $this->saberClient = $this->scanClass->getCachedReader()->getClassAnnotation($reflectionClass, SaberClient::class);
        $this->requestMapping = $this->scanClass->getClassAndInterfaceAnnotation($reflectionClass, RequestMapping::class);
        if ($this->requestMapping == null) {
            throw new SaberCloudException($reflectionClass->getName() . " missing RequestMapping Annotation");
        }
        foreach ($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            $this->reflectionMethods[$reflectionMethod->getName()] = $reflectionMethod;
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|string
     * @throws CloudException
     * @throws ParamException
     * @throws RouteException
     * @throws SaberCloudException
     * @throws \ReflectionException
     * @throws BadResponseException
     */
    public function __call($name, $arguments)
    {
        $this->response = null;
        $serviceName = $this->saberClient->value ?? $this->saberClient->host;
        //存在断路器，并且断路了，就执行降级
        if ($this->circuitBreaker != null) {
            try {
                $available = $this->circuitBreaker->isAvailable($serviceName);
            } catch (\Throwable $e) {
                $this->warn("CircuitBreaker is enable ,but has error on running : {$e->getMessage()}");
                $available = true;
            }
            if (!$available) {
                if (class_exists($this->saberClient->fallback)) {
                    return call_user_func_array([DIGet($this->saberClient->fallback), $name], $arguments);
                }
                throw new BadResponseException("Circuit Breaker");
            }
        }
        if ($this->saberClient->value != null) {
            $saber = $this->saberCloud->getSaber($this->saberClient->value);
        } elseif ($this->saberClient->host != null) {
            $saber = $this->saberCloud->getSaberFromBaseUrl($this->saberClient->host);
        } else {
            throw new SaberCloudException("value and host can not be all null");
        }
        $reflectionMethod = $this->reflectionMethods[$name];
        $parameters = $reflectionMethod->getParameters();
        //重新获取入参格式
        $nameArguments = [];
        foreach ($parameters as $parameter) {
            $nameArguments[$parameter->getName()] = $arguments[$parameter->getPosition()] ?? $parameter->getDefaultValue();
        }

        /** @var RequestMapping $requestMapping */
        $requestMapping = $this->scanClass->getMethodAndInterfaceAnnotation($reflectionMethod, RequestMapping::class);
        if ($requestMapping == null) {
            throw new SaberCloudException("method $name missing RequestMapping annotation");
        }

        //配置所有参数
        $stdParameters = [];
        $options = [];
        foreach ($this->scanClass->getMethodAndInterfaceAnnotations($reflectionMethod) as $annotation) {
            if ($annotation instanceof PathVariable) {
                $result = $nameArguments[$annotation->param ?? $annotation->value];
                if ($annotation->required && $result == null) {
                    if ($result == null) throw new RouteException("path {$annotation->value} not find");
                }
                $stdParameters[$annotation->value] = $result;
            } else if ($annotation instanceof RequestParam) {
                $result = $nameArguments[$annotation->param ?? $annotation->value];
                if ($annotation->required && $result == null) {
                    throw new ParamException("require params $annotation->value");
                }
                $options['uri_query'][$annotation->value] = $result;
            } else if ($annotation instanceof RequestFormData) {
                $result = $nameArguments[$annotation->param ?? $annotation->value];
                if ($annotation->required && $result == null) {
                    throw new ParamException("require params $annotation->value");
                }
                $options['data'][$annotation->value] = $result;
                $options['content_type'] = ContentType::MULTIPART;
            } else if ($annotation instanceof RequestRawJson || $annotation instanceof RequestBody) {
                $result = $nameArguments[$annotation->value];
                $options['data'] = json_decode(json_encode($result), true);
                $options['content_type'] = ContentType::JSON;
            } else if ($annotation instanceof ModelAttribute) {
                $result = $nameArguments[$annotation->value];
                $options['data'] = json_decode(json_encode($result), true);
                $options['content_type'] = ContentType::MULTIPART;
            } else if ($annotation instanceof RequestRaw) {
                $result = $nameArguments[$annotation->value];
                $options['data'] = $result;
            } else if ($annotation instanceof RequestRawXml) {
                $result = $nameArguments[$annotation->value];
                $options['data'] = $result;
                $options['content_type'] = ContentType::XML;
            }
        }
        //解析URL
        $stdUrl = $this->getCache("stdUrl", function () use ($requestMapping) {
            return $this->std->parse($requestMapping->value);
        });
        $routeUrls = [];
        foreach ($stdUrl as $road) {
            $success = true;
            $routeUrl = "";
            foreach ($road as $value) {
                if (is_array($value)) {
                    if (array_key_exists($value[0], $stdParameters)) {
                        $routeUrl .= $stdParameters[$value[0]];
                    } else {
                        $success = false;
                        break;
                    }
                } else {
                    $routeUrl .= $value;
                }
            }
            if ($success) {
                $routeUrls[] = $routeUrl;
            }
        }
        if (empty($routeUrls)) throw new SaberCloudException("can not build route url");
        $options['uri'] = "/" . trim(trim($this->requestMapping->value, "/") . "/" . trim(array_pop($routeUrls), "/"), "/");
        $options['method'] = strtoupper($requestMapping->method[0]);
        //请求
        $this->response = $saber->request($options);
        if ($this->response->getStatusCode() >= 400 && $this->response->getStatusCode() < 500 && !$this->saberClient->decode404) {
            //断路器failure
            if ($this->circuitBreaker != null) {
                try {
                    $this->circuitBreaker->failure($serviceName);
                }catch (\Throwable $e){
                    $this->warn("CircuitBreaker is enable ,but has error on running : {$e->getMessage()}");
                }
            }
            //存在降级执行降级函数
            if (class_exists($this->saberClient->fallback)) {
                return call_user_func_array([DIGet($this->saberClient->fallback), $name], $arguments);
            }
            throw new RouteException($this->response->getStatusCode());
        }
        if ($this->response->getStatusCode() >= 500 || $this->response->getStatusCode() < 0) {
            //断路器failure
            if ($this->circuitBreaker != null) {
                try {
                    $this->circuitBreaker->failure($serviceName);
                }catch (\Throwable $e){
                    $this->warn("CircuitBreaker is enable ,but has error on running : {$e->getMessage()}");
                }
            }
            //存在降级执行降级函数
            if (class_exists($this->saberClient->fallback)) {
                return call_user_func_array([DIGet($this->saberClient->fallback), $name], $arguments);
            }
            throw new BadResponseException();
        }
        //断路器success
        if ($this->circuitBreaker != null) {
            try {
                $this->circuitBreaker->success($serviceName);
            }catch (\Throwable $e){
                $this->warn("CircuitBreaker is enable ,but has error on running : {$e->getMessage()}");
            }
        }
        /** @var ResponseBody $responseBody */
        $responseBody = $this->scanClass->getMethodAndInterfaceAnnotation($reflectionMethod, ResponseBody::class);
        if ($responseBody != null) {
            return json_decode($this->response->getBody()->__toString(), true);
        } else {
            return $this->response->getBody()->__toString();
        }
    }

    /**
     * 获取缓存值
     * @param $name
     * @param callable $fuc
     * @return mixed|null
     */
    private function getCache($name, callable $fuc)
    {
        $result = $this->cache[$name] ?? null;
        if ($result == null) {
            $result = $fuc();
            $this->cache[$name] = $result;
        }
        return $result;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}