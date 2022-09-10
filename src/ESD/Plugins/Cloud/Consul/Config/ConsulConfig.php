<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Config;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Server\Config\PortConfig;
use ESD\Server\Coroutine\Server;

/**
 * Class ConsulConfig
 * @package ESD\Plugins\Cloud\Consul\Config
 */
class ConsulConfig extends BaseConfig
{
    const KEY = "consul";

    /**
     * Default host
     * @var string
     */
    protected $host = "http://127.0.0.1:8500";

    /**
     * @var ConsulServiceConfig[]|null
     */
    protected $serviceConfigs;

    /**
     * The tags registered by default will override the tags configuration in ConsulServiceConfig
     * @var string[]|null
     */
    protected $defaultTags;

    /**
     * Tag of the default query service
     * @var string|null
     */
    protected $defaultQueryTag;

    /**
     * Query service tag comparison table
     * @var string[]|null
     */
    protected $serverListQueryTags;

    /**
     * Local network card device
     * @var string
     */
    protected $bindNetDev = "eth0";

    /**
     * Leader name
     * @var string|null
     */
    protected $leaderName;

    /**
     * ConsulConfig constructor.
     * @param string $host
     * @throws \ReflectionException
     */
    public function __construct(?string $host)
    {
        parent::__construct(self::KEY);
        if ($host != null) {
            $this->host = $host;
        }
    }

    /**
     * Get server ip
     *
     * @param $dev
     * @return string
     */
    private function getServerIp($dev)
    {
        return exec("ip -4 addr show $dev | grep inet | awk '{print $2}' | cut -d / -f 1");
    }

    /**
     * Automatic configuration
     *
     * @throws ConfigException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public function autoConfig()
    {
        $serverConfig = Server::$instance->getServerConfig();
        $normalName = $serverConfig->getName();
        $ip = $this->getServerIp($this->getBindNetDev());
        if (empty($this->getServiceConfigs())) {

            //If ServiceConfigs is not configured then the configuration will be populated automatically
            foreach (Server::$instance->getPortManager()->getPortConfigs() as $portConfig) {
                $protocol = "http";
                if ($portConfig->isOpenHttpProtocol()) {
                    $protocol = "http";
                    if ($portConfig->isEnableSsl()) {
                        $protocol = "https";
                    }
                } elseif ($portConfig->isOpenWebsocketProtocol()) {
                    $protocol = "ws";
                    if ($portConfig->isEnableSsl()) {
                        $protocol = "wss";
                    }
                } elseif ($portConfig->getSockType() == PortConfig::SWOOLE_SOCK_TCP || $portConfig->getSockType() == PortConfig::SWOOLE_SOCK_TCP6) {
                    $protocol = "tcp";
                }

                //Set up a service config
                $consulServiceConfig = new ConsulServiceConfig();
                $consulServiceConfig->setName($normalName);
                $consulServiceConfig->setId($normalName . "-" . $ip . "-" . $portConfig->getPort());
                $consulServiceConfig->setAddress($ip);
                $consulServiceConfig->setPort($portConfig->getPort());
                $consulServiceConfig->setMeta(["server" => "esd", "protocol" => $protocol]);
                $consulCheckConfig = new ConsulCheckConfig();
                $consulCheckConfig->setInterval("10s");
                $consulCheckConfig->setTlsSkipVerify(true);
                $consulCheckConfig->setNotes("esd auto check");
                $consulCheckConfig->setStatus("passing");
                $consulServiceConfig->setCheckConfig($consulCheckConfig);

                if ($portConfig->isOpenHttpProtocol() || $portConfig->isOpenWebsocketProtocol()) {
                    $consulCheckConfig->setHttp("$protocol://$ip:{$portConfig->getPort()}/actuator/health");
                    $this->addServiceConfig($consulServiceConfig);
                } elseif ($protocol == "tcp") {
                    $consulCheckConfig = new ConsulCheckConfig();
                    $consulCheckConfig->setTcp("$protocol://$ip:{$portConfig->getPort()}");
                    $this->addServiceConfig($consulServiceConfig);
                }
            }
        }

        //Modify global configuration
        foreach ($this->getServiceConfigs() as $consulServiceConfig) {
            if (empty($consulServiceConfig->getName())) {
                throw new ConfigException("Consul service config missing name field");
            }
            if (!empty($this->getDefaultTags())) {
                $consulServiceConfig->setTags($this->getDefaultTags());
            }
        }
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param ConsulServiceConfig $consulServiceConfig
     */
    public function addServiceConfig(ConsulServiceConfig $consulServiceConfig)
    {
        if ($this->serviceConfigs == null) $this->serviceConfigs = [];
        $this->serviceConfigs[$consulServiceConfig->getName()] = $consulServiceConfig;
    }

    /**
     * @return ConsulServiceConfig[]|null
     */
    public function getServiceConfigs(): ?array
    {
        return $this->serviceConfigs;
    }

    /**
     * @param ConsulServiceConfig[]|null $serviceConfigs
     */
    public function setServiceConfigs(?array $serviceConfigs): void
    {
        if (empty($serviceConfigs)) {
            $this->serviceConfigs = $serviceConfigs;
        } else {
            foreach ($serviceConfigs as $key => $value) {
                if (is_array($value)) {
                    $this->serviceConfigs[$key] = new ConsulServiceConfig();
                    $this->serviceConfigs[$key]->setName($key);
                    $this->serviceConfigs[$key]->buildFromConfig($value);
                } else if ($value instanceof ConsulServiceConfig) {
                    $this->serviceConfigs[$key] = $value;
                }
            }
        }
    }

    /**
     * @return string[]|null
     */
    public function getDefaultTags(): ?array
    {
        return $this->defaultTags;
    }

    /**
     * @param string[]|null $defaultTags
     */
    public function setDefaultTags(?array $defaultTags): void
    {
        $this->defaultTags = $defaultTags;
    }

    /**
     * @return string|null
     */
    public function getDefaultQueryTag(): ?string
    {
        return $this->defaultQueryTag;
    }

    /**
     * @param string|null $defaultQueryTag
     */
    public function setDefaultQueryTag(?string $defaultQueryTag): void
    {
        $this->defaultQueryTag = $defaultQueryTag;
    }

    /**
     * @return string[]|null
     */
    public function getServerListQueryTags(): ?array
    {
        return $this->serverListQueryTags;
    }

    /**
     * @param string[]|null $serverListQueryTags
     */
    public function setServerListQueryTags(?array $serverListQueryTags): void
    {
        $this->serverListQueryTags = $serverListQueryTags;
    }

    /**
     * @return string
     */
    public function getBindNetDev(): string
    {
        return $this->bindNetDev;
    }

    /**
     * @param string $bindNetDev
     */
    public function setBindNetDev(string $bindNetDev): void
    {
        $this->bindNetDev = $bindNetDev;
    }

    /**
     * @return string|null
     */
    public function getLeaderName(): ?string
    {
        return $this->leaderName;
    }

    /**
     * @param string|null $leaderName
     */
    public function setLeaderName(?string $leaderName): void
    {
        $this->leaderName = $leaderName;
    }

}