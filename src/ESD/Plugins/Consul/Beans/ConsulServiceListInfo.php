<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul\Beans;


class ConsulServiceListInfo
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var ConsulServiceInfo[]
     */
    private $consulServiceInfos;

    /**
     * ConsulServiceListInfo constructor.
     * @param string $serviceName
     * @param array $consulServiceInfos
     */
    public function __construct(string $serviceName, array $consulServiceInfos)
    {
        $this->serviceName = $serviceName;
        $this->consulServiceInfos = $consulServiceInfos;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return ConsulServiceInfo[]
     */
    public function getConsulServiceInfos(): array
    {
        return $this->consulServiceInfos;
    }

    /**
     * @param ConsulServiceInfo[] $consulServiceInfos
     */
    public function setConsulServiceInfos(array $consulServiceInfos): void
    {
        $this->consulServiceInfos = $consulServiceInfos;
    }
}