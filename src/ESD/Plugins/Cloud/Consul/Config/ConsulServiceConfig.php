<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Config;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class ConsulServiceConfig
 * @package ESD\Plugins\Cloud\Consul\Config
 */
class ConsulServiceConfig extends BaseConfig
{
    const KEY = "consul.service_configs";

    /**
     * Service name, default will be server name
     * @var string|null
     */
    protected $name;

    /**
     * Specify a unique ID for this service. Each agent must be unique. If Name is not provided, it defaults to the parameter.
     * @var string|null
     */
    protected $id;

    /**
     * Specify a list of tags to assign to the service.
     * These tags can be used for future filtering and exposed through the API.
     * @var string[]|null
     */
    protected $tags;

    /**
     * Specify the address of the service. If not provided, the address of the proxy is used as the address of the service during a DNS query.
     * @var string|null
     */
    protected $address;

    /**
     * Specify the port of the service.
     * @var int|null
     */
    protected $port;

    /**
     * Specify any KV metadata linked to the service instance.
     * @var string[]|null
     */
    protected $meta;

    /**
     * @var ConsulCheckConfig|null
     */
    protected $checkConfig;

    /**
     * ConsulServiceConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string[]|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param string[]|null $tags
     */
    public function setTags(?array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     */
    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string[]|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * @param string[]|null $meta
     */
    public function setMeta(?array $meta): void
    {
        $this->meta = $meta;
    }


    /**
     * @return ConsulCheckConfig|null
     */
    public function getCheckConfig(): ?ConsulCheckConfig
    {
        return $this->checkConfig;
    }

    /**
     * @param ConsulCheckConfig|null $checkConfig
     * @throws \ReflectionException
     */
    public function setCheckConfig($checkConfig): void
    {
        if (is_array($checkConfig)) {
            $this->checkConfig = new ConsulCheckConfig();
            $this->checkConfig->buildFromConfig($checkConfig);
        } elseif ($checkConfig instanceof ConsulCheckConfig) {
            $this->checkConfig = $checkConfig;
        }
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function buildConfig(): array
    {
        return array_filter([
            "Name" => $this->getName(),
            "ID" => $this->getId(),
            "Tags" => $this->getTags(),
            "Address" => $this->getAddress(),
            "Meta" => $this->getMeta(),
            "Port" => $this->getPort(),
            "Check" => $this->buildCheckConfigs()
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildCheckConfigs()
    {
        if (empty($this->checkConfig)) {
            return null;
        }
        return $this->checkConfig->buildConfig();
    }

}