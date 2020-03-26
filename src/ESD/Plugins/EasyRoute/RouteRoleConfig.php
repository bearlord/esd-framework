<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute;


use ESD\Core\Plugins\Config\BaseConfig;

class RouteRoleConfig extends BaseConfig
{
    const key = "route.role";
    /**
     * 名称
     * @var string
     */
    protected $name;
    /**
     * 路由
     * @var string
     */
    protected $route;
    /**
     * 控制器类名
     * @var string
     */
    protected $controller;
    /**
     * 控制器方法名
     * @var string
     */
    protected $method;
    /**
     * 访问类型，GET，POST
     * @var string
     */
    protected $type;
    /**
     * 放行端口类型
     * @var array
     */
    protected $portTypes = [];
    /**
     * 放行端口名
     * @var array
     */
    protected $portNames = [];

    public function __construct()
    {
        parent::__construct(self::key, true, "name");
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }


    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller): void
    {
        $this->controller = $controller;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    public function buildName()
    {
        $this->name = $this->type . "_" . $this->route;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = "/" . trim($route, "/");
    }

    /**
     * @return array
     */
    public function getPortTypes(): array
    {
        return $this->portTypes;
    }

    /**
     * @param array $portTypes
     */
    public function setPortTypes(array $portTypes): void
    {
        $this->portTypes = $portTypes;
    }

    /**
     * @return array
     */
    public function getPortNames(): array
    {
        return $this->portNames;
    }

    /**
     * @param array $portNames
     */
    public function setPortNames(array $portNames): void
    {
        $this->portNames = $portNames;
    }

}