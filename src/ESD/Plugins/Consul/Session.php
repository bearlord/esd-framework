<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\SessionInterface;

/**
 * Class Session
 * @package ESD\Consul
 */
class Session implements SessionInterface
{
    private $client;

    /**
     * Session constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @param null $body
     * @param array $options
     * @return mixed
     */
    public function create($body = null, array $options = array())
    {
        $params = array(
            'body' => $body,
            'query' => OptionsResolver::resolve($options, array('dc')),
        );

        return $this->client->put('/v1/session/create', $params);
    }

    /**
     * @param $sessionId
     * @param array $options
     * @return mixed
     */
    public function destroy($sessionId, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('dc')),
        );

        return $this->client->put('/v1/session/destroy/' . $sessionId, $params);
    }

    /**
     * @param $sessionId
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function info($sessionId, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc')),
        );

        return $this->client->get('/v1/session/info/' . $sessionId, $params);
    }

    /**
     * @param $node
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function node($node, array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc')),
        );

        return $this->client->get('/v1/session/node/' . $node, $params);
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function all(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait', 'dc')),
        );

        return $this->client->get('/v1/session/list', $params);
    }

    /**
     * @param $sessionId
     * @param array $options
     * @return mixed
     */
    public function renew($sessionId, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('dc')),
        );

        return $this->client->put('/v1/session/renew/' . $sessionId, $params);
    }
}
