<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 13:56
 */

namespace ESD\Consul;

use ESD\GuzzleSaber\SaberClient;
use Psr\Log\LoggerInterface;
use SensioLabs\Consul\Client;
use SensioLabs\Consul\Services\AgentInterface;
use SensioLabs\Consul\Services\CatalogInterface;
use SensioLabs\Consul\Services\HealthInterface;
use SensioLabs\Consul\Services\KVInterface;
use SensioLabs\Consul\Services\SessionInterface;

final class ServiceFactory
{
    private static $services = array(
        AgentInterface::class => Agent::class,
        CatalogInterface::class => Catalog::class,
        HealthInterface::class => Health::class,
        SessionInterface::class => Session::class,
        KVInterface::class => KV::class,

        // for backward compatibility:
        AgentInterface::SERVICE_NAME => Agent::class,
        CatalogInterface::SERVICE_NAME => Catalog::class,
        HealthInterface::SERVICE_NAME => Health::class,
        SessionInterface::SERVICE_NAME => Session::class,
        KVInterface::SERVICE_NAME => KV::class,
    );

    private $client;

    public function __construct(array $options = array(), LoggerInterface $logger = null)
    {
        $this->client = new Client($options, $logger, new SaberClient($options));
    }

    public function get($service)
    {
        if (!array_key_exists($service, self::$services)) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not available. Pick one among "%s".', $service, implode('", "', array_keys(self::$services))));
        }

        $class = self::$services[$service];

        return new $class($this->client);
    }
}
