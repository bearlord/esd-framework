<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\Exception\ServerException;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\AgentInterface;
use SensioLabs\Consul\ConsulResponse;

/**
 * Class Agent
 * @package ESD\Consul
 */
class Agent implements AgentInterface
{

    private $client;

    /**
     * Agent constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * Checks
     * @return ConsulResponse
     */
    public function checks()
    {
        return $this->client->get('/v1/agent/checks');
    }

    /**
     * Services
     * @return ConsulResponse
     */
    /**
     * @return ConsulResponse|ServerException
     */
    public function services()
    {
        return $this->client->get('/v1/agent/services');
    }

    /**
     * Service
     * @param array $options
     * @param int $timeout
     * @return ConsulResponse
     */
    public function service(array $options = [], int $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, ['index', 'wait']),
        );
        return $this->client->get('/v1/agent/service', $params);
    }

    /**
     * Members
     * @param array $options
     * @return ConsulResponse
     */
    public function members(array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['wan']),
        );

        return $this->client->get('/v1/agent/members', $params);
    }

    /**
     * Self
     * @return ConsulResponse
     */
    public function self()
    {
        return $this->client->get('/v1/agent/self');
    }

    /**
     * Join
     * @param string $address
     * @param array $options
     * @return ConsulResponse
     */
    public function join(string $address, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['wan']),
        );

        return $this->client->get('/v1/agent/join/' . $address, $params);
    }

    /**
     * Force leave
     * @param string $node
     * @return ConsulResponse
     */
    public function forceLeave(string $node)
    {
        return $this->client->get('/v1/agent/force-leave/' . $node);
    }

    /**
     * Register check
     * @param $check
     * @return ConsulResponse
     */
    public function registerCheck($check)
    {
        $params = array(
            'body' => $check,
        );

        return $this->client->put('/v1/agent/check/register', $params);
    }

    /**
     * Deregister check
     * @param string $checkId
     * @return ConsulResponse
     */
    public function deregisterCheck(string $checkId)
    {
        return $this->client->put('/v1/agent/check/deregister/' . $checkId);
    }

    /**
     * Pass check
     * Pass check
     * @param string $checkId
     * @param array $options
     * @return mixed
     */
    public function passCheck(string $checkId, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['note']),
        );

        return $this->client->put('/v1/agent/check/pass/' . $checkId, $params);
    }

    /**
     * Warn check
     * @param string $checkId
     * @param array $options
     * @return ConsulResponse
     */
    public function warnCheck(string $checkId, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['note']),
        );

        return $this->client->put('/v1/agent/check/warn/' . $checkId, $params);
    }

    /**
     * Fail check
     * @param string $checkId
     * @param array $options
     * @return ConsulResponse
     */
    public function failCheck(string $checkId, array $options = [])
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, ['note']),
        );

        return $this->client->put('/v1/agent/check/fail/' . $checkId, $params);
    }

    /**
     * Register service
     * @param $service
     * @return ConsulResponse
     */
    public function registerService($service)
    {
        $params = array(
            'body' => $service,
        );

        return $this->client->put('/v1/agent/service/register', $params);
    }

    /**
     * Deregister service
     * @param string $serviceId
     * @return ConsulResponse
     */
    public function deregisterService(string $serviceId)
    {
        return $this->client->put('/v1/agent/service/deregister/' . $serviceId);
    }
}
