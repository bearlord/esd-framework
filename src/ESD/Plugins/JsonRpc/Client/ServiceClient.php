<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Client;

use ESD\LoadBalance\LoadBalancerManager;
use ESD\Plugins\JsonRpc\DataFormatter;
use ESD\Plugins\JsonRpc\Protocol;
use ESD\Plugins\JsonRpc\RpcException;
use ESD\Rpc\Client\AbstractServiceClient;
use ESD\Rpc\IdGenerator\IdGeneratorInterface;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Yii;
use RuntimeException;

/**
 * Class ServiceClient
 * @package ESD\Plugins\JsonRpc\Client
 */
class ServiceClient extends AbstractServiceClient
{
    /**
     * @var string The service name of the target service.
     */
    public $serviceName = '';

    /**
     * @var string The protocol of the target service
     */
    public $protocol = '';

    /**
     * @var string
     */
    public $loadBanlance = 'random';

    /**
     * @var string;
     */
    public $idGenerator = 'ESD\Rpc\IdGenerator\UniqidIdGenerator';

    /**
     * @var Client
     */
    public $client;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->client = Yii::createObject([
            'class' => Client::class,
            'protocol' => $this->getProtocol(),
            'config' => $this->getConfig()
        ]);
    }

    

    /**
     * @param string $idGenerator
     */
    public function setIdGenerator(string $idGenerator): void
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @return string
     */
    protected function getIdGenerator(): string
    {
        if (empty($this->idGenerator)) {
            $config = $this->getConfig();

            $this->idGenerator = !empty($config['idGenerator']) ? $config['idGenerator'] : 'ESD\Rpc\IdGenerator\UniqidIdGenerator';
        }

        return $this->idGenerator;
    }

    /**
     * @return IdGeneratorInterface
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getIdGeneratorObject(): IdGeneratorInterface
    {
        return Yii::createObject($this->getIdGenerator());
    }

    /**
     * @return DataFormatter
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    protected function getDataFormatter()
    {
        return Yii::createObject(DataFormatter::class);
    }

    /**
     * @param string $methodName
     * @return string
     */
    protected function generateRpcPath(string $methodName): string
    {
        if (!$this->serviceName) {
            throw new InvalidArgumentException('Parameter $serviceName missing.');
        }

        return sprintf("%s/%s", $this->serviceName, $methodName);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string|null $id
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    protected function generateData(string $method, array $params, ?string $id)
    {
        $path = $this->generateRpcPath($method);

        return $this->getDataFormatter()->formatRequest([$path, $params, $id]);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string|null $id
     */
    public function request(string $method, array $params, ?string $id = null)
    {
        if (!$id && $this->getIdGeneratorObject() instanceof IdGeneratorInterface) {
            $id = $this->getIdGeneratorObject()->generate();
        }

        $response = $this->client->send($this->generateData($method, $params, $id));
        if (!is_array($response)) {
            throw new RpcException('Invalid response.');
        }
    }





    public function __call($name, $params)
    {
        return $this->request($name, $params);
    }
}