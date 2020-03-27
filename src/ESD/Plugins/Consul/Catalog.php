<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\CatalogInterface;

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
     * @param $node
     * @return mixed
     */
    public function register($node)
    {
        $params = array(
            'body' => (string)$node,
        );

        return $this->client->put('/v1/catalog/register', $params);
    }

    /**
     * @param $node
     * @return mixed
     */
    public function deregister($node)
    {
        $params = array(
            'body' => (string)$node,
        );

        return $this->client->put('/v1/catalog/deregister', $params);
    }

    /**
     * @return mixed
     */
    public function datacenters()
    {
        return $this->client->get('/v1/catalog/datacenters');
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function nodes(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/nodes', $params);
    }

    /**
     * @param $node
     * @param array $options
     * @return mixed
     */
    public function node($node, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('dc', 'filter')),
        );

        return $this->client->get('/v1/catalog/node/' . $node, $params);
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function services(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'node-meta')),
        );

        return $this->client->get('/v1/catalog/services', $params);
    }

    /**
     * @param $service
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function service($service, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/service/' . $service, $params);
    }

    /**
     * @param $service
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function connect($service, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/connect/' . $service, $params);
    }
}
