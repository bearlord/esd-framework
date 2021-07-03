<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Rpc\Client;

use ESD\Core\Exception;
use ESD\Core\Server\Server;
use ESD\Plugins\JsonRpc\Protocol;
use ESD\Yii\Base\Component;
use ESD\Yii\Helpers\ArrayHelper;
use ESD\Yii\Yii;
use IdGeneratorInterface;

/**
 * Class AbstractServiceClient
 * @package ESD\Rpc\Client
 */
abstract class AbstractServiceClient extends Component
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
     * @var array
     */
    public $nodes = [];

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var string
     */
    public $port = '';

    /**
     * @var Client
     */
    public $client;

    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        if (empty($this->protocol)) {
            $config = $this->getConfig();
            $this->protocol = !empty($config['protocol']) ? $config['protocol'] : Protocol::PROTOCOL_JSON_RPC_HTTP;
        }

        return $this->protocol;
    }

    /**
     * @throws \Exception
     */
    public function getConfig()
    {
        $config = Server::$instance->getConfigContext()->get('yii.consumers');
        if (empty($config)) {
            throw new RpcException("Consumers config not found");
        }

        $indexConfig = ArrayHelper::index($config, 'name');
        if (empty($indexConfig[$this->serviceName])) {
            throw new RpcException(sprintf("Consumers.%s config not found"), $this->serviceName);
        }

        return $indexConfig[$this->serviceName];
    }

}