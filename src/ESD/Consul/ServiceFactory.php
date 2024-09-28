<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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

/**
 * Class ServiceFactory
 * @package ESD\Consul
 */
final class ServiceFactory
{
    /**
     * @var array $services
     */
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

    /**
     * @var Client
     */
    private $client;

    /**
     * ServiceFactory constructor.
     * @param array $options
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $options = array(), LoggerInterface $logger = null)
    {
        $this->client = new Client($options, $logger, new SaberClient($options));
    }

    /**
     * Get service
     * @param $service
     * @return mixed
     */
    public function get($service)
    {
        if (!array_key_exists($service, self::$services)) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not available. Pick one among "%s".', $service, implode('", "', array_keys(self::$services))));
        }

        $class = self::$services[$service];

        return new $class($this->client);
    }
}
