<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\HealthInterface;
use SensioLabs\Consul\ConsulResponse;

/**
 * Class Health
 * @package ESD\Consul
 */
class Health implements HealthInterface
{
    private $client;

    /**
     * Health constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * Node
     * @param $node
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function node($node, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'filter')),
        );

        return $this->client->get('/v1/health/node/' . $node, $params);
    }

    /**
     * Checks
     * @param $service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function checks($service, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/health/checks/' . $service, $params);
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
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'tag', 'node-meta', 'passing', 'filter')),
        );

        return $this->client->get('/v1/health/service/' . $service, $params);
    }

    /**
     * Connect
     * @param $connect
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function connect($connect, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'tag', 'node-meta', 'passing', 'filter')),
        );

        return $this->client->get('/v1/health/connect/' . $connect, $params);
    }

    /**
     * State
     * @param $state
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function state($state, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc', 'near', 'node-meta', 'filter')),
        );

        return $this->client->get('/v1/health/state/' . $state, $params);
    }
}