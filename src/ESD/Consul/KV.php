<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\KVInterface;
use SensioLabs\Consul\ConsulResponse;

/**
 * Class KV
 * @package ESD\Consul
 */
class KV implements KVInterface
{
    private $client;

    /**
     * KV constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * Get
     * @param $key
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function get($key, array $options = [], $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait', 'dc', 'recurse', 'keys', 'separator', 'raw', 'stale', 'consistent', 'default']),
        );

        return $this->client->get('v1/kv/' . $key, $params);
    }

    /**
     * Put
     * @param $key
     * @param $value
     * @param array $options
     * @return ConsulResponse
     */
    public function put($key, $value, array $options = [])
    {
        $params = array(
            'body' => (string)$value,
            'query' => OptionsResolver::resolve($options, ['dc', 'flags', 'cas', 'acquire', 'release']),
        );

        return $this->client->put('v1/kv/' . $key, $params);
    }

    /**
     * Delete
     * @param $key
     * @param array $options
     * @return ConsulResponse
     */
    public function delete($key, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['dc', 'recurse']),
        );

        return $this->client->delete('v1/kv/' . $key, $params);
    }
}
