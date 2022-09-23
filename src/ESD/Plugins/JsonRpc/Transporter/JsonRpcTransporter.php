<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Transporter;

use ESD\Core\Context\Context;
use ESD\Server\Coroutine\Server;
use ESD\LoadBalance\Algorithm\Random;
use ESD\LoadBalance\Algorithm\RoundRobin;
use ESD\LoadBalance\Algorithm\WeightedRandom;
use ESD\LoadBalance\Algorithm\WeightedRoundRobin;
use ESD\LoadBalance\LoadBalancerInterface;
use ESD\LoadBalance\Node;
use ESD\Yii\Base\Component;
use Swoole\Coroutine\Client as SwooleClient;
use RuntimeException;

/**
 * Class JsonRpcTransporter
 * @package ESD\Plugins\JsonRpc\Transporter
 */
class JsonRpcTransporter extends Component implements TransporterInterface
{
    public $config = [];

    /**
     * The service name of the target service.
     *
     * @var string
     */
    protected $serviceName = '';

    /**
     * @var float
     */
    public $connectTimeout = 15;

    /**
     * @var float
     */
    public $receiveTimeout = 15;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var array
     */
    public $nodes = [];

    /**
     * Static node, skip loadBalance and select a node from the nodes
     * @var Node
     */
    public $node;

    /**
     * @var null|LoadBalancerInterface|Random|RoundRobin|WeightedRandom|WeightedRoundRobin
     */
    private $loadBalancer;

    /**
     * The load balancer of the client, this name of the load balancer
     * needs to register into \ESD\LoadBalancer\LoadBalancerManager.
     *
     * @var string
     */
    public $loadBalancerAlgorithm = 'random';

    /**
     * JsonRpcHttpTransporter constructor.
     * @param array $config
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->serviceName = $config['serviceName'];
        $this->nodes = $config['nodes'];

        if (!empty($config['connectTimeout'])) {
            $this->connectTimeout = $config['connectTimeout'];
        }
        if (!empty($config['receiveTimeout'])) {
            $this->receiveTimeout = $config['receiveTimeout'];
        }
        if (!empty($config['loadBalancer'])) {
            $this->loadBalancerAlgorithm = $config['loadBalancer'];
        }
    }

    /**
     * @param array $nodes
     */
    public function setNodes(array $nodes): void
    {
        $this->nodes = $nodes;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * @param array $node
     */
    public function setNode(array $node)
    {
        if (!is_int($node['port'])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid node config [%s], the port option has to a integer.',
                implode(':', $node)));
        }
        $schema = $node['schema'] ?? null;
        $path = $node['path'] ?? null;
        $weight = $node['weight'] ?? 0;
        $this->node = new Node($schema, $node['host'], $node['port'], $path, $weight);
    }

    /**
     * If the load balancer is exists, then the node will select by the load balancer,
     * otherwise will get a random node.
     */
    public function getNode(): Node
    {
        if (!empty($this->node)) {
            return $this->node;
        }

        if (empty($this->loadBalancer)) {
            $loadBalancer = $this->createLoadBalancer($this->createNodes());
            $this->setLoadBalancer($loadBalancer);
        }

        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
    }

    /**
     * @param string $data
     * @return mixed
     * @throws RuntimeException
     */
    public function send(string $data)
    {
        $client = retry(2, function () use ($data) {
            $client = $this->getClient();

            if ($client->send($data) === false) {
                if ($client->errCode == 104) {
                    throw new RuntimeException('Connect to server failed.');
                }
            }
            return $client;
        });

        return $this->receiveAndCheck($client, $this->receiveTimeout);
    }
    
    /**
     * @return string
     */
    public function recv()
    {
        $client = $this->getClient();

        return $this->receiveAndCheck($client, $this->receiveTimeout);
    }

    /**
     * @return LoadBalancerInterface|null
     */
    public function getLoadBalancer(): ?LoadBalancerInterface
    {
        return $this->loadBalancer;
    }

    /**
     * @param LoadBalancerInterface $loadBalancer
     * @return TransporterInterface
     */
    public function setLoadBalancer(LoadBalancerInterface $loadBalancer): TransporterInterface
    {
        $this->loadBalancer = $loadBalancer;
        return $this;
    }

    /**
     * @return SwooleClient
     * @throws RuntimeException
     */
    public function getClient(): SwooleClient
    {
        $class = spl_object_hash($this) . '.Connection';

        $contextClient = getContextValue($class);
        if (getContextValue($class)) {
            return $contextClient;
        }

        $contextClient = retry(2, function () {
            $node = $this->getNode();
            $swooleClient = new SwooleClient(SWOOLE_SOCK_TCP);
            $swooleClient->set($this->config['settings'] ?? []);
            $result = $swooleClient->connect($node->host, $node->port, $this->connectTimeout);

            if ($result === false && ($swooleClient->errCode == 114 or $swooleClient->errCode == 115)) {
                // Force close and reconnect to server.
                $swooleClient->close();
                throw new RuntimeException('Connect to server failed.');
            }
            return $swooleClient;
        });
        setContextValue($class, $contextClient);
        
        return $contextClient;
    }

    /**
     * @param SwooleClient $client
     * @param float $timeout
     * @return string
     */
    public function receiveAndCheck(\Swoole\Coroutine\Client $client, float $timeout)
    {
        $data = $client->recv((float) $timeout);
        if ($data === '') {
            // RpcConnection: When the next time the connection is taken out of the connection pool, it will reconnecting to the target service.
            // Client: It will reconnecting to the target service in the next request.
            $client->close();
            throw new RuntimeException('Connection is closed. ' . $client->errMsg, $client->errCode);
        }
        if ($data === false) {
            $client->close();
            throw new RuntimeException('Error receiving data, errno=' . $client->errCode . ' errmsg=' . swoole_strerror($client->errCode), $client->errCode);
        }

        return $data;
    }
}