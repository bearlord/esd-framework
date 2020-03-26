<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\RouteTool;

use ESD\Core\ParamException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\EasyRoute\Annotation\ModelAttribute;
use ESD\Plugins\EasyRoute\Annotation\PathVariable;
use ESD\Plugins\EasyRoute\Annotation\RequestBody;
use ESD\Plugins\EasyRoute\Annotation\RequestFormData;
use ESD\Plugins\EasyRoute\Annotation\RequestParam;
use ESD\Plugins\EasyRoute\Annotation\RequestRaw;
use ESD\Plugins\EasyRoute\Annotation\RequestRawJson;
use ESD\Plugins\EasyRoute\Annotation\RequestRawXml;
use ESD\Plugins\EasyRoute\Annotation\ResponseBody;
use ESD\Plugins\EasyRoute\EasyRouteConfig;
use ESD\Plugins\EasyRoute\EasyRoutePlugin;
use ESD\Plugins\EasyRoute\MethodNotAllowedException;
use ESD\Plugins\EasyRoute\RouteException;
use ESD\Plugins\Pack\ClientData;
use ESD\Plugins\Validate\Annotation\ValidatedFilter;
use FastRoute\Dispatcher;

class AnnotationRoute implements IRoute
{
    use GetLogger;

    /**
     * @var ClientData
     */
    private $clientData;

    /**
     * @inheritDoc
     * @param ClientData $data
     * @param EasyRouteConfig $easyRouteConfig
     * @return bool
     * @throws MethodNotAllowedException
     * @throws ParamException
     * @throws RouteException
     */
    public function handleClientData(ClientData $data, EasyRouteConfig $easyRouteConfig): bool
    {
        $this->clientData = $data;
        $port = $this->clientData->getClientInfo()->getServerPort();
        $routeInfo = EasyRoutePlugin::$instance->getDispatcher()->dispatch($port . ":" . $this->clientData->getRequestMethod(), $this->clientData->getPath());
        $request = $this->clientData->getRequest();

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new RouteException("{$this->clientData->getPath()} 404 Not found");
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                if ($this->clientData->getRequest()->getMethod() == "OPTIONS") {
                    $methods = [];
                    foreach ($routeInfo[1] as $value) {
                        list($port, $method) = explode(":", $value);
                        $methods[] = $method;
                    }
                    $this->clientData->getResponse()->withHeader("Access-Control-Allow-Methods", implode(",", $methods));
                    $this->clientData->getResponse()->end();
                    return false;
                } else {
                    throw new MethodNotAllowedException("Method not allowed");
                }

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->clientData->setControllerName($handler[0]->name);
                $this->clientData->setMethodName($handler[1]->name);
                $params = [];
                $methodReflection = $handler[1]->getReflectionMethod();
                foreach (EasyRoutePlugin::$instance->getScanClass()->getMethodAndInterfaceAnnotations($methodReflection) as $annotation) {
                    if ($annotation instanceof ResponseBody) {
                        $data->getResponse()->withHeader("Content-Type", $annotation->value);
                    }
                    if ($annotation instanceof PathVariable) {
                        $result = $vars[$annotation->value] ?? null;
                        if ($annotation->required) {
                            if ($result == null) throw new RouteException("path {$annotation->value} not find");
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestParam) {
                        if ($request == null) continue;
                        $result = $request->query($annotation->value);
                        if ($annotation->required && $result == null) {
                            throw new ParamException("require params $annotation->value");
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestFormData) {
                        if ($request == null) continue;
                        $result = $request->post($annotation->value);
                        if ($annotation->required && $result == null) {
                            throw new ParamException("require params $annotation->value");
                        }
                        $params[$annotation->param ?? $annotation->value] = $result;
                    } else if ($annotation instanceof RequestRawJson || $annotation instanceof RequestBody) {
                        if ($request == null) continue;
                        if (!$json = json_decode($request->getBody()->getContents(), true)) {
                            $this->warning('RequestRawJson errror, raw:' . $request->getBody()->getContents());
                            throw new RouteException('RawJson Format error');
                        }
                        $params[$annotation->value] = $json;
                    } else if ($annotation instanceof ModelAttribute) {
                        if ($request == null) continue;
                        $params[$annotation->value] = $request->post();
                    } else if ($annotation instanceof RequestRaw) {
                        if ($request == null) continue;
                        $raw = $request->getBody()->getContents();
                        $params[$annotation->value] = $raw;
                    } else if ($annotation instanceof RequestRawXml) {
                        if ($request == null) continue;
                        $raw = $request->getBody()->getContents();
                        if (!$xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS)) {
                            $this->warning('RequestRawXml errror, raw:' . $request->getBody()->getContents());
                            throw new RouteException('RawXml Format error');
                        }
                        $params[$annotation->value] = json_decode(json_encode($xml), true);
                    }
                }
                $realParams = [];
                if ($methodReflection instanceof \ReflectionMethod) {
                    foreach ($methodReflection->getParameters() as $parameter) {
                        if ($parameter->getClass() != null) {
                            $values = $params[$parameter->name];
                            if ($values != null) {
                                $values = ValidatedFilter::valid($parameter->getClass(), $values);
                                $instance = $parameter->getClass()->newInstance();
                                foreach ($instance as $key => $value) {
                                    $instance->$key = $values[$key] ?? null;
                                }
                                $realParams[$parameter->getPosition()] = $instance;
                            } else {
                                $realParams[$parameter->getPosition()] = null;
                            }
                        } else {
                            $realParams[$parameter->getPosition()] = $params[$parameter->name];
                        }
                    }
                }
                if (!empty($realParams)) {
                    $this->clientData->setParams($realParams);
                }
                break;
        }
        return true;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getControllerName()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getControllerName();
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getMethodName()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getMethodName();
    }

    /**
     * @inheritDoc
     * @return string|null
     */
    public function getPath()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getPath();
    }

    /**
     * @inheritDoc
     * @return array|null
     */
    public function getParams()
    {
        if ($this->clientData == null) return null;
        return $this->clientData->getParams();
    }

    /**
     * Get client data
     *
     * @return ClientData
     */
    public function getClientData(): ?ClientData
    {
        return $this->clientData;
    }
}