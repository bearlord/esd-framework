<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Port;

use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Server;

/**
 * Class PortManager
 * @package ESD\Core\Server\Port
 */
class PortManager
{
    /**
     * @var PortConfig[]
     */
    private $portConfigs = [];

    /**
     * @var ServerPort[]
     */
    private $ports = [];

    /**
     * @var ServerPort[]
     */
    private $namePorts = [];

    /**
     * @var Server
     */
    private $server;

    /**
     * @var string
     */
    private $defaultPortClass;

    /**
     * PortManager constructor.
     * @param Server $server
     * @param string $defaultPortClass
     */
    public function __construct(Server $server, string $defaultPortClass)
    {
        $this->server = $server;
        $this->defaultPortClass = $defaultPortClass;
    }

    /**
     * Add port config
     *
     * @param $name
     * @param PortConfig $portConfig
     * @param null $portClass
     */
    public function addPortConfig($name, PortConfig $portConfig, $portClass = null)
    {
        $portConfig->setName($name);
        if ($portClass != null) {
            $portConfig->setPortClass($portClass);
        }
        $this->portConfigs[$name] = $portConfig;
    }

    /**
     * Merge config
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function mergeConfig()
    {
        foreach ($this->portConfigs as $portConfig) {
            $portConfig->merge();
        }
    }

    /**
     * Get port config
     * @param $port
     * @return PortConfig|null
     * @throws \Exception
     */
    public function getPortConfig($port): ?PortConfig
    {
        $configs = Server::$instance->getConfigContext()->get(PortConfig::KEY);
        foreach ($configs as $key => $value) {
            if ($value['port'] === $port) {
                $portConfig = new PortConfig();
                $portConfig->setName($key);
                $portConfig->buildFromConfig($value);
                return $portConfig;
            }
        }

        return null;
    }

    /**
     * Port configs
     *
     * @return PortConfig[]
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function getPortConfigs(): array
    {
        $this->mergeConfig();

        //Reacquire configuration
        $portConfigs = [];
        $configs = Server::$instance->getConfigContext()->get(PortConfig::KEY);
        foreach ($configs as $key => $value) {
            $portConfig = new PortConfig();
            $portConfig->setName($key);
            $portConfigs[$key] = $portConfig->buildFromConfig($value);
        }
        return $portConfigs;
    }

    /**
     * Create a port instance
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function createPorts()
    {
        //Reacquire configuration
        $this->portConfigs = $this->getPortConfigs();
        if (count($this->portConfigs) == 0) {
            throw new ConfigException("Missing port configuration, unable to start service");
        }
        foreach ($this->portConfigs as $portConfig) {
            $portClass = $portConfig->getPortClass();
            if ($portClass == null) {
                $serverPort = new $this->defaultPortClass($this->server, $portConfig);
            } else {
                $serverPort = new $portClass($this->server, $portConfig);
            }

            if (isset($this->ports[$portConfig->getPort()])) {
                throw new ConfigException("Duplicate port numbers");
            }
            if (!$serverPort instanceof ServerPort) {
                throw new ConfigException("Port instance must extend ServerPort");
            }

            Server::$instance->getContainer()->injectOn($serverPort);

            $this->ports[$portConfig->getPort()] = $serverPort;
            $this->namePorts[$portConfig->getName()] = $serverPort;
        }
    }

    /**
     * @return ServerPort[]
     */
    public function getPorts(): array
    {
        return $this->ports;
    }

    /**
     * Get port instance with corresponding port number
     *
     * @param int $portNo
     * @return ServerPort|null
     */
    public function getPortFromPortNo(int $portNo): ?ServerPort
    {
        return $this->ports[$portNo] ?? null;
    }

    /**
     * Get port instance with corresponding port number
     *
     * @param string $name
     * @return ServerPort|null
     */
    public function getPortFromName(string $name): ?ServerPort
    {
        return $this->namePorts[$name] ?? null;
    }

    /**
     * Whether to has WebSocket port
     *
     * @return bool
     */
    public function hasWebSocketPort(): bool
    {
        foreach ($this->ports as $port) {
            if ($port->getPortConfig()->isOpenWebsocketProtocol()) return true;
        }
        return false;
    }

    /**
     * Whether to has http port
     * @return bool
     */
    public function hasHttpPort(): bool
    {
        foreach ($this->ports as $port) {
            if ($port->getPortConfig()->isOpenHttpProtocol()) return true;
        }
        return false;
    }

    /**
     * Get default port class
     *
     * @return string
     */
    public function getDefaultPortClass(): string
    {
        return $this->defaultPortClass;
    }

    /**
     * Get port from fd
     *
     * @param int $fd
     * @return ServerPort|null
     */
    public function getPortFromFd(int $fd): ?ServerPort
    {
        $clientInfo = Server::$instance->getClientInfo($fd);

        $serverPort = $clientInfo->getServerPort();
        if (empty($serverPort)) {
            return null;
        }

        return $this->getPortFromPortNo($serverPort);
    }
}
