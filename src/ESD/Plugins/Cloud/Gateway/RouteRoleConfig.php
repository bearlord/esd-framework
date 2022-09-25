<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class RouteRoleConfig
 * @package ESD\Plugins\Cloud\Gateway
 */
class RouteRoleConfig extends BaseConfig
{
    const KEY = "route.role";
    
    /**
     * Name
     * @var string
     */
    protected $name;

    /**
     * Route
     * @var string
     */
    protected $route;

    /**
     * Controller
     * @var string
     */
    protected $controller;

    /**
     * Method
     * @var string
     */
    protected $method;

    /**
     * Type
     * @var string
     */
    protected $type;

    /**
     * Allowed port types
     * @var array
     */
    protected $portTypes = [];

    /**
     * Allow port names
     * @var array
     */
    protected $portNames = [];

    /**
     * RouteRoleConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY, true, "name");
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