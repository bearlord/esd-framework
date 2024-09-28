<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\SessionInterface;
use SensioLabs\Consul\ConsulResponse;

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
     * Session create
     * @param null $body
     * @param array $options
     * @return ConsulResponse
     */
    public function create($body = null, array $options = [])
    {
        $params = array(
            'body' => $body,
            'query' => OptionsResolver::resolve($options, ['dc']),
        );

        return $this->client->put('/v1/session/create', $params);
    }

    /**
     * Session destroy
     * @param $sessionId
     * @param array $options
     * @return ConsulResponse
     */
    public function destroy($sessionId, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['dc']),
        );

        return $this->client->put('/v1/session/destroy/' . $sessionId, $params);
    }

    /**
     * Session info
     * @param $sessionId
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function info($sessionId, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc']),
        );

        return $this->client->get('/v1/session/info/' . $sessionId, $params);
    }

    /**
     * Seesion node
     * @param $node
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function node($node, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc']),
        );

        return $this->client->get('/v1/session/node/' . $node, $params);
    }

    /**
     * Session list
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function all(array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc']),
        );

        return $this->client->get('/v1/session/list', $params);
    }

    /**
     * Session renew
     * @param $sessionId
     * @param array $options
     * @return ConsulResponse
     */
    public function renew($sessionId, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['dc']),
        );

        return $this->client->put('/v1/session/renew/' . $sessionId, $params);
    }
}
