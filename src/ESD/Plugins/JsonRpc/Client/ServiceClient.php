<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Client;

use ESD\Plugins\JsonRpc\DataFormatter;
use ESD\Rpc\Client\AbstractServiceClient;
use ESD\Rpc\IdGenerator\IdGeneratorInterface;
use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Yii;

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
    public $protocol = 'jsonrpc-http';

    /**
     * @var string
     */
    public $loadBanlance = 'random';

    /**
     * @var string;
     */
    public $idGenerator = 'ESD\Rpc\IdGenerator\UniqidIdGenerator';

    protected $pathGenerator;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var string
     */
    private $_transport = 'ESD\Yii\JsonRpc\Transporter\StreamTransport';

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * @param string $idGenerator
     */
    public function setIdGenerator(string $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @return IdGeneratorInterface
     */
    protected function getIdGenerator() :IdGeneratorInterface
    {
        $config = $this->getConfig();
        $idGenerator = !empty($config['idGenerator']) ? $config['idGenerator'] : '';
        if (empty($idGenerator)) {
            $idGenerator = $this->idGenerator;
        }

        return Yii::createObject($idGenerator);
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
    protected function request(string $method, array $params, ?string $id = null)
    {
        if (!$id && $this->getIdGenerator() instanceof IdGeneratorInterface) {
            $id = $this->getIdGenerator()->generate();
        }

        $this->client = Yii::createObject(Client::class);
        var_dump($this->client, $id);

        $this->client->send($this->generateData($method, $params, $id));
    }



    public function __call($name, $params)
    {
        return $this->request($name, $params);
    }
}