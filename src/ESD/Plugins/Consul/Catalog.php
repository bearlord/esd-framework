<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/25
 * Time: 15:28
 */

namespace ESD\Consul;


use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\CatalogInterface;

class Catalog implements CatalogInterface
{
    private $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function register($node)
    {
        $params = array(
            'body' => (string)$node,
        );

        return $this->client->put('/v1/catalog/register', $params);
    }

    public function deregister($node)
    {
        $params = array(
            'body' => (string)$node,
        );

        return $this->client->put('/v1/catalog/deregister', $params);
    }

    public function datacenters()
    {
        return $this->client->get('/v1/catalog/datacenters');
    }

    public function nodes(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/nodes', $params);
    }

    public function node($node, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('dc', 'filter')),
        );

        return $this->client->get('/v1/catalog/node/' . $node, $params);
    }

    public function services(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'node-meta')),
        );

        return $this->client->get('/v1/catalog/services', $params);
    }

    public function service($service, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/service/' . $service, $params);
    }

    public function connect($service, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'tag', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/catalog/connect/' . $service, $params);
    }
}
