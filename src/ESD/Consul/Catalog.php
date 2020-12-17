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
     * @param $node
     * @return ConsulResponse
     */
    public function register($node)
    {
        $params = [
            'body' => (string)$node,
        ];

        return $this->client->put('/v1/catalog/register', $params);
    }

    /**
     * @param $node
     * @return ConsulResponse
     */
    public function deregister($node)
    {
        $params = [
            'body' => (string)$node,
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
     * Nodes
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function nodes(array $options = [], $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc', 'near', 'node-meta', 'filter']),
        ];

        return $this->client->get('/v1/catalog/nodes', $params);
    }

    /**
     * Node
     * @param $node
     * @param array $options
     * @return ConsulResponse
     */
    public function node($node, array $options = [])
    {
        $params = [
            'query' => OptionsResolver::resolve($options, ['dc', 'filter']),
        ];

        return $this->client->get('/v1/catalog/node/' . $node, $params);
    }

    /**
     * Services
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function services(array $options = [], $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'node-meta')),
        ];

        return $this->client->get('/v1/catalog/services', $params);
    }

    /**
     * Service
     * @param $service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function service($service, array $options = [], $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        ];

        return $this->client->get('/v1/catalog/service/' . $service, $params);
    }

    /**
     * Connect
     * @param $service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function connect($service, array $options = [], $timeout = 5)
    {
        $params = [
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        ];

        return $this->client->get('/v1/catalog/connect/' . $service, $params);
    }
}
