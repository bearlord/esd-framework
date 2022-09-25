<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Server\Coroutine\Server;

/**
 * Class RouteConfig
 * @package ESD\Plugins\Cloud\Gateway
 */
class RouteConfig extends BaseConfig
{
    const KEY = "gateway-route";

    /**
     * @var string
     */
    protected $errorControllerName = NormalErrorController::class;

    /**
     * @var RouteRoleConfig[]
     */
    protected $routeRoles = [];

    /**
     * RouteConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return RouteRoleConfig[]
     */
    public function getRouteRoles(): array
    {
        return $this->routeRoles;
    }

    /**
     * @param RouteRoleConfig[] $routeRoles
     */
    public function setRouteRoles(array $routeRoles): void
    {
        foreach ($routeRoles as $name => $role) {
            if ($role instanceof RouteRoleConfig) {
                $this->routeRoles[$name] = $role;
            } else {
                $roleConfig = new RouteRoleConfig();
                $roleConfig->buildFromConfig($role);
                $roleConfig->setName($name);
                $this->routeRoles[$name] = $roleConfig;
            }
        }
    }

    /**
     * @param RouteRoleConfig $roleConfig
     * @throws \Exception
     */
    public function addRouteRole(RouteRoleConfig $roleConfig)
    {
        if (array_key_exists($roleConfig->getName(), $this->routeRoles)) {
            $routeRoles = $this->routeRoles[$roleConfig->getName()];
            if ($routeRoles) {
                if (!empty(array_intersect($routeRoles->getPortNames(), $roleConfig->getPortNames()))) {
                    Server::$instance->getLog()->warning(sprintf("Duplicate route： %s", $roleConfig->getName()));
                }
            }
        }
        $this->routeRoles[$roleConfig->getName()] = $roleConfig;
    }

    /**
     * @return string
     */
    public function getErrorControllerName(): string
    {
        return $this->errorControllerName;
    }

    /**
     * @param string $errorControllerName
     */
    public function setErrorControllerName(string $errorControllerName): void
    {
        $this->errorControllerName = $errorControllerName;
    }
}
