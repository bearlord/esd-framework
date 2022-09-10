<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul\Config;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class ConsulCheckConfig
 * @package ESD\Plugins\Cloud\Consul\Config
 */
class ConsulCheckConfig extends BaseConfig
{
    const KEY = "consul.service_configs.[].check_config";

    /**
     * Specify how often to run this check. This is required for HTTP and TCP checks.
     * @var string|null
     */
    protected $interval;

    /**
     * Arbitrary notes
     * @var string|null
     */
    protected $notes;

    /**
     * Specifies that inspections associated with the service after this time should be deregistered.
     * This is specified as a suffixed duration, such as "10m".
     * If a check is in a critical state and exceeds this configured value,
     * its associated service (and all its related checks) will be automatically unregistered.
     * The minimum timeout is 1 minute. The process of acquiring critical services runs every 30 seconds,
     * so it may take slightly longer than the configured timeout to trigger deregistration.
     * You should generally configure a timeout that is much longer than any expected recoverable interruption for a given service.
     * @var string|null
     */
    protected $deregisterCriticalServiceAfter;

    /**
     * Specify the inspection endpoint that gRPC supports the standard gRPC health check protocol.
     * Interval checks the configured endpoints to update the check status in the given state.
     * @var string|null
     */
    protected $gRPC;

    /**
     * Specify whether to use TLS for this gRPC health check.
     * If TLS is enabled, a valid TLS certificate is required by default.
     * You can turn off certificate verification by setting TLSSkipVerify to true.
     * @var bool|null
     */
    protected $gRPCUseTLS;

    /**
     * Specify HTTP check to perform a Request Interval for each value HTTP (expected URL) with GET.
     * If the response is any 2xx code, the check is passing. If it is responding to 429 Too Many Requests, the check is warning.
     * Otherwise, inspection is critical. HTTP inspection also supports SSL. A valid SSL certificate is required by default.
     * You can use TLSSkipVerify. To control certificate verification.
     * @var string|null
     */
    protected $http;

    /**
     * Specify other HTTP methods for HTTP inspection.
     * If no value is specified, GET is used.
     * @var string|null
     */
    protected $method;

    /**
     * Specifies a set of headers that should be set for HTTP inspection.
     * Each header can have multiple values.
     * @var string[]|null
     */
    protected $header;

    /**
     * Specify timeout for outgoing connection in case of script, HTTP, TCP or gRPC check.
     * Can be specified in the form of "10s" or "5m" (that is, 10 seconds or 5 minutes, respectively).
     * @var string|null
     */
    protected $timeout;

    /**
     * Specify whether the certificate checked by HTTPS should not be verified.
     * @var bool|null
     */
    protected $tlsSkipVerify;

    /**
     * Specify a TCP connection value TCP (expected IP or host name plus port combination) Interval.
     * If the connection attempt is successful, the check is passing. If the connection attempt fails, the check is critical.
     * If the host name resolves to an IPv4 and IPv6 address, an attempt will be made to both addresses,
     * and the first successful connection attempt will result in a successful check.
     * @var string|null
     */
    protected $tcp;

    /**
     * Specify that this is a TTL check, and the status of the check must be updated periodically using a TTL endpoint.
     * @var string|null
     */
    protected $ttl;

    /**
     * Specify the initial state of the health check.
     * @var string|null
     */
    protected $status;

    /**
     * ConsulCheckConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return string|null
     */
    public function getInterval(): ?string
    {
        return $this->interval;
    }

    /**
     * @param string|null $interval
     */
    public function setInterval(?string $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getTtl(): ?string
    {
        return $this->ttl;
    }

    /**
     * @param string|null $ttl
     */
    public function setTtl(?string $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string|null
     */
    public function getTcp(): ?string
    {
        return $this->tcp;
    }

    /**
     * @param string|null $tcp
     */
    public function setTcp(?string $tcp): void
    {
        $this->tcp = $tcp;
    }

    /**
     * @return bool|null
     */
    public function getTlsSkipVerify(): ?bool
    {
        return $this->tlsSkipVerify;
    }

    /**
     * @param bool|null $tlsSkipVerify
     */
    public function setTlsSkipVerify(?bool $tlsSkipVerify): void
    {
        $this->tlsSkipVerify = $tlsSkipVerify;
    }

    /**
     * @return string|null
     */
    public function getTimeout(): ?string
    {
        return $this->timeout;
    }

    /**
     * @param string|null $timeout
     */
    public function setTimeout(?string $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string[]|null
     */
    public function getHeader(): ?array
    {
        return $this->header;
    }

    /**
     * @param string[]|null $header
     */
    public function setHeader(?array $header): void
    {
        $this->header = $header;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string|null $method
     */
    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return string|null
     */
    public function getHttp(): ?string
    {
        return $this->http;
    }

    /**
     * @param string|null $http
     */
    public function setHttp(?string $http): void
    {
        $this->http = $http;
    }

    /**
     * @return bool|null
     */
    public function getGRPCUseTLS(): ?bool
    {
        return $this->gRPCUseTLS;
    }

    /**
     * @param bool|null $gRPCUseTLS
     */
    public function setGRPCUseTLS(?bool $gRPCUseTLS): void
    {
        $this->gRPCUseTLS = $gRPCUseTLS;
    }

    /**
     * @return string|null
     */
    public function getGRPC(): ?string
    {
        return $this->gRPC;
    }

    /**
     * @param string|null $gRPC
     */
    public function setGRPC(?string $gRPC): void
    {
        $this->gRPC = $gRPC;
    }

    /**
     * @return string|null
     */
    public function getDeregisterCriticalServiceAfter(): ?string
    {
        return $this->deregisterCriticalServiceAfter;
    }

    /**
     * @param string|null $deregisterCriticalServiceAfter
     */
    public function setDeregisterCriticalServiceAfter(?string $deregisterCriticalServiceAfter): void
    {
        $this->deregisterCriticalServiceAfter = $deregisterCriticalServiceAfter;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @inheritDoc
     */
    public function buildConfig()
    {
        return array_filter([
            "Interval" => $this->getInterval(),
            "Notes" => $this->getNotes(),
            "DeregisterCriticalServiceAfter" => $this->getDeregisterCriticalServiceAfter(),
            "GRPC" => $this->getGRPC(),
            "GRPCUseTLS" => $this->getGRPCUseTLS(),
            "HTTP" => $this->getHttp(),
            "Method" => $this->getMethod(),
            "Header" => $this->getHeader(),
            "Timeout" => $this->getTimeout(),
            "TLSSkipVerify" => $this->getTlsSkipVerify(),
            "TCP" => $this->getTcp(),
            "TTL" => $this->getTtl(),
            "Status" => $this->getStatus()
        ]);
    }

}