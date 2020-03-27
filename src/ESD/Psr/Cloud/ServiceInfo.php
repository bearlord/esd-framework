<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Psr\Cloud;

/**
 * Class ServiceInfo
 * @package ESD\Psr\Cloud
 */
class ServiceInfo
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $serviceId;
    /**
     * @var string
     */
    private $serviceAddress;
    /**
     * @var string
     */
    private $servicePort;

    /**
     * @var string
     */
    private $serviceAgreement;
    /**
     * @var null|string[]
     */
    private $serviceMeta;

    /**
     * @var null|string[]
     */
    private $serviceTags;

    /**
     * ServiceInfo constructor.
     * @param $serviceName
     * @param $serviceId
     * @param $serviceAddress
     * @param $servicePort
     * @param $serviceMeta
     * @param $serviceTags
     * @param $serviceAgreement
     */
    public function __construct($serviceName, $serviceId, $serviceAddress, $servicePort, $serviceMeta, $serviceTags, $serviceAgreement)
    {
        $this->serviceName = $serviceName;
        $this->serviceId = $serviceId;
        $this->serviceAddress = $serviceAddress;
        $this->servicePort = $servicePort;
        $this->serviceMeta = $serviceMeta;
        $this->serviceTags = $serviceTags;
        $this->serviceAgreement = $serviceAgreement;
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
     * @return string
     */
    public function getServiceAddress(): string
    {
        return $this->serviceAddress;
    }

    /**
     * @param string $serviceAddress
     */
    public function setServiceAddress(string $serviceAddress): void
    {
        $this->serviceAddress = $serviceAddress;
    }

    /**
     * @return string
     */
    public function getServicePort(): string
    {
        return $this->servicePort;
    }

    /**
     * @param string $servicePort
     */
    public function setServicePort(string $servicePort): void
    {
        $this->servicePort = $servicePort;
    }

    /**
     * @return string[]|null
     */
    public function getServiceMeta(): ?array
    {
        return $this->serviceMeta;
    }

    /**
     * @param string[]|null $serviceMeta
     */
    public function setServiceMeta(?array $serviceMeta): void
    {
        $this->serviceMeta = $serviceMeta;
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * @param string $serviceId
     */
    public function setServiceId(string $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @param string[]|null $serviceTags
     * @return ServiceInfo
     */
    public function setServiceTags(?array $serviceTags): ServiceInfo
    {
        $this->serviceTags = $serviceTags;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getServiceTags(): ?array
    {
        return $this->serviceTags;
    }

    /**
     * @return string
     */
    public function getServiceAgreement(): string
    {
        return $this->serviceAgreement;
    }

    /**
     * @param string $serviceAgreement
     */
    public function setServiceAgreement(string $serviceAgreement): void
    {
        $this->serviceAgreement = $serviceAgreement;
    }
}