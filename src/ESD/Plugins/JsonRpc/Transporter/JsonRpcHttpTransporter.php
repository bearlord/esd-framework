<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Transporter;

use ESD\LoadBalance\Algorithm\Random;
use ESD\LoadBalance\Algorithm\RoundRobin;
use ESD\LoadBalance\Algorithm\WeightedRandom;
use ESD\LoadBalance\Algorithm\WeightedRoundRobin;
use ESD\LoadBalance\LoadBalancerInterface;
use ESD\LoadBalance\LoadBalancerManager;
use ESD\LoadBalance\Node;
use ESD\Yii\Base\BaseObject;
use ESD\Yii\Base\Component;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Helpers\Json;
use ESD\Yii\HttpClient\Client;
use ESD\Yii\HttpClient\CurlFormatter;
use ESD\Yii\HttpClient\CurlTransport;
use ESD\Yii\Yii;
use Swlib\Saber;
use Swoole\Coroutine\Channel;


/**
 * Class JsonRpcHttpTransporter
 * @package ESD\Plugins\JsonRpc\Transporter
 */
class JsonRpcHttpTransporter extends Component implements TransporterInterface
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
    public $connectTimeout = 5;

    /**
     * @var float
     */
    public $receiveTimeout = 5;

    /**
     * If $loadBalancer is null, will select a node in $nodes to request,
     * otherwise, use the nodes in $loadBalancer.
     *
     * @var array
     */
    public $nodes = [];

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
        $this->serviceName = $config['name'];
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

        $loadBalancer = $this->createLoadBalancer($this->createNodes());
        $this->setLoadBalancer($loadBalancer);
    }

    /**
     * Create nodes the first time.
     *
     * @return array [array, callable]
     */
    protected function createNodes(): array
    {
        $consumer = $this->config;

        // Not exists the registry config, then looking for the 'nodes' property.
        if (isset($consumer['nodes'])) {
            $nodes = [];
            foreach ($consumer['nodes'] ?? [] as $item) {
                if (isset($item['host'], $item['port'])) {
                    if (!is_int($item['port'])) {
                        throw new InvalidArgumentException(sprintf('Invalid node config [%s], the port option has to a integer.', implode(':', $item)));
                    }
                    $schema = $item['schema'] ?? null;
                    $path = $item['path'] ?? null;
                    $weigth = $item['weight'] ?? 0;
                    $nodes[] = new Node($schema, $item['host'], $item['port'], $path, $weigth);
                }
            }
            return $nodes;
        }

        throw new InvalidArgumentException('Config of registry or nodes missing.');
    }

    /**
     * @param array $nodes
     * @return \ESD\LoadBalance\LoadBalancerInterface
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function createLoadBalancer(array $nodes)
    {
        /** @var LoadBalancerManager $loadBalanceManager */
        $loadBalanceManager = Yii::createObject(LoadBalancerManager::class);
        $loadBalance = $loadBalanceManager->getInstance($this->serviceName, $this->loadBalancerAlgorithm)->setNodes($nodes);

        return $loadBalance;
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

    private function getEof()
    {
        return "\r\n";
    }

    /**
     * If the load balancer is exists, then the node will select by the load balancer,
     * otherwise will get a random node.
     */
    private function getNode(): Node
    {
        if ($this->loadBalancer instanceof LoadBalancerInterface) {
            return $this->loadBalancer->select();
        }
        return $this->nodes[array_rand($this->nodes)];
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
     * @param string $data
     * @return mixed
     */
    public function send(string $data)
    {
        $node = $this->getNode();
        $url = sprintf("%s://%s:%s/%s",
            $node->getSchema(),
            $node->getHost(),
            $node->getPort(),
            $node->getPath()
        );

        enableRuntimeCoroutine(true, SWOOLE_HOOK_ALL);
        $channel = new Channel(1);
        goWithContext(function () use ($channel, $url, $node, $data) {
            $saber = Saber::create([
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                ]
            ]);
            $responeData = $saber->post($url, $data)->getBody();
            $channel->push($responeData);

            $this->loadBalancer->removeNode($node);
        });

        $response = $channel->pop();
        return $response;
    }

    public function recv()
    {

    }


}