<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\CatalogInterface;
use SensioLabs\Consul\ConsulResponse;

/**
 * Class Catalog
 * @package ESD\Consul
 */
class Catalog implements CatalogInterface
{
    private $client;

    /**
     * Catalog constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * register
     * @param string $node
     * @return ConsulResponse
     */
    public function register(string $node)
    {
        $params = [
            'body' => $node,
        ];

        return $this->client->put('/v1/catalog/register', $params);
    }

    /**
     * @param string $node
     * @return ConsulResponse
     */
    public function deregister(string $node)
    {
        $params = [
            'body' => $node,
        ];

        return $this->client->put('/v1/catalog/deregister', $params);
    }

    /**
     * Data centers
     * @return ConsulResponse
     */
    public function datacenters()
    {
        return $this->client->get('/v1/catalog/datacenters');
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function nodes(array $options = [], int $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc', 'near', 'node-meta', 'filter']),
        ];

        return $this->client->get('/v1/catalog/nodes', $params);
    }

    /**
     * @param string $node
     * @param array $options
     * @return ConsulResponse
     */
    public function node(string $node, array $options = [])
    {
        $params = [
            'query' => OptionsResolver::resolve($options, ['dc', 'filter']),
        ];

        return $this->client->get('/v1/catalog/node/' . $node, $params);
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function services(array $options = [], int $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'node-meta')),
        ];

        return $this->client->get('/v1/catalog/services', $params);
    }

    /**
     * @param string $service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function service(string $service, array $options = [], int $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        ];

        return $this->client->get('/v1/catalog/service/' . $service, $params);
    }

    /**
     * @param string $service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function connect(string $service, array $options = [], int $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        ];

        return $this->client->get('/v1/catalog/connect/' . $service, $params);
    }
}
