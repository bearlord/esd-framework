<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Consul;

use SensioLabs\Consul\Client;
use SensioLabs\Consul\OptionsResolver;
use SensioLabs\Consul\Services\AgentInterface;

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
     * @return mixed
     */
    public function checks()
    {
        return $this->client->get('/v1/agent/checks');
    }

    /**
     * @return mixed
     */
    public function services()
    {
        return $this->client->get('/v1/agent/services');
    }

    /**
     * @param array $options
     * @param int $timeout
     * @return mixed
     */
    public function service(array $options = array(), $timeout = 5)
    {
        $params = array(
            'timeout' => $timeout,
            'query' => OptionsResolver::resolve($options, array('index', 'wait')),
        );
        return $this->client->get('/v1/agent/service', $params);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function members(array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('wan')),
        );

        return $this->client->get('/v1/agent/members', $params);
    }

    /**
     * @return mixed
     */
    public function self()
    {
        return $this->client->get('/v1/agent/self');
    }

    /**
     * @param $address
     * @param array $options
     * @return mixed
     */
    public function join($address, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('wan')),
        );

        return $this->client->get('/v1/agent/join/' . $address, $params);
    }

    /**
     * @param $node
     * @return mixed
     */
    public function forceLeave($node)
    {
        return $this->client->get('/v1/agent/force-leave/' . $node);
    }

    /**
     * @param $check
     * @return mixed
     */
    public function registerCheck($check)
    {
        $params = array(
            'body' => $check,
        );

        return $this->client->put('/v1/agent/check/register', $params);
    }

    /**
     * @param $checkId
     * @return mixed
     */
    public function deregisterCheck($checkId)
    {
        return $this->client->put('/v1/agent/check/deregister/' . $checkId);
    }

    /**
     * @param $checkId
     * @param array $options
     * @return mixed
     */
    public function passCheck($checkId, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('note')),
        );

        return $this->client->put('/v1/agent/check/pass/' . $checkId, $params);
    }

    /**
     * @param $checkId
     * @param array $options
     * @return mixed
     */
    public function warnCheck($checkId, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('note')),
        );

        return $this->client->put('/v1/agent/check/warn/' . $checkId, $params);
    }

    /**
     * @param $checkId
     * @param array $options
     * @return mixed
     */
    public function failCheck($checkId, array $options = array())
    {
        $params = array(
            'query' => OptionsResolver::resolve($options, array('note')),
        );

        return $this->client->put('/v1/agent/check/fail/' . $checkId, $params);
    }

    /**
     * @param $service
     * @return mixed
     */
    public function registerService($service)
    {
        $params = array(
            'body' => $service,
        );

        return $this->client->put('/v1/agent/service/register', $params);
    }

    /**
     * @param $serviceId
     * @return mixed
     */
    public function deregisterService($serviceId)
    {
        return $this->client->put('/v1/agent/service/deregister/' . $serviceId);
    }
}
