<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Client;

use ESD\Core\DI\DI;
use ESD\LoadBalance\LoadBalancerManager;
use ESD\Plugins\JsonRpc\DataFormatter;
use ESD\Plugins\JsonRpc\Protocol;
use ESD\Plugins\JsonRpc\RequestException;
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
    protected $config;

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
        $this->setConfig($config);
        parent::__construct($config);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array|mixed $config
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }


    /**
     * @return Client
     */
    public function getClient()
    {
        if (empty($this->client)) {
            $this->client = Yii::createObject(Client::class, [
                $this->getConfig(),
                $this->getProtocol()
            ]);
        }
        return $this->client;
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

            $this->idGenerator = !empty($config['idGenerator']) ? $config['idGenerator'] : \ESD\Rpc\IdGenerator\UniqidIdGenerator::class;
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

        $response = $this->getClient()->send($this->generateData($method, $params, $id));
        if (!is_array($response)) {
            throw new RequestException('Invalid response.');
        }

        $response = $this->checkRequestIdAndTryAgain($response, $id);

        if (array_key_exists('result', $response)) {
            return $response['result'];
        }
        if (array_key_exists('error', $response)) {
            return $response['error'];
        }
    }

    /**
     * @param array $response
     * @param $id
     * @param int $again
     * @return array
     */
    protected function checkRequestIdAndTryAgain(array $response, $id, int $again = 1): array
    {
        if (is_null($id)) {
            // If the request id is null then do not check.
            return $response;
        }

        if (isset($response['id']) && $response['id'] === $id) {
            return $response;
        }

        if ($again <= 0) {
            throw new RequestException(sprintf(
                'Invalid response. Request id[%s] is not equal to response id[%s].',
                $id,
                $response['id'] ?? null
            ));
        }

        $response = $this->client->recv();
        --$again;

        return $this->checkRequestIdAndTryAgain($response, $id, $again);
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed|void
     * @throws RpcException
     */
    public function __call(string $name, array $params)
    {
        return $this->request($name, $params);
    }
}
