<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\Plugins\JsonRpc\Client;

use ESD\Core\Server\Server;
use ESD\Plugins\JsonRpc\Packer\JsonEofPacker;
use ESD\Plugins\JsonRpc\Packer\JsonLengthPacker;
use ESD\Plugins\JsonRpc\Packer\JsonPacker;
use ESD\Plugins\JsonRpc\Packer\PackerInterface;
use ESD\Plugins\JsonRpc\Protocol;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcHttpTransporter;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcPoolTransporter;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcStreamTransport;
use ESD\Plugins\JsonRpc\Transporter\JsonRpcTransporter;
use ESD\Plugins\JsonRpc\Transporter\TransporterInterface;
use ESD\Rpc\Client\AbstractServiceClient;
use ESD\Yii\Base\Component;
use ESD\Yii\HttpClient\Transport;
use ESD\Yii\Yii;

/**
 * Class Client
 * @package ESD\Plugins\JsonRpc\Client
 */
class Client extends Component
{
    /**
     * @var string
     */
    public $protocol = '';

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var null|PackerInterface
     */
    protected $packer;

    /**
     * @var null|TransporterInterface
     */
    protected $transporter;


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
            $this->protocol = Protocol::PROTOCOL_JSON_RPC_HTTP;
        }

        return $this->protocol;
    }



    /**
     * @return Protocol
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getProtocolObject(): Protocol
    {
        return Yii::createObject(Protocol::class);
    }

    /**
     * @return string
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getPacker()
    {
        $protocol = $this->getProtocolObject();

        return $protocol->getPacker($this->protocol);
    }

    /**
     * @return PackerInterface|JsonEofPacker|JsonLengthPacker|JsonPacker
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getPackerObject()
    {
        $packer = $this->getPacker();

        $params = [
            'class' => $packer
        ];
        if (!empty($this->config['setting'])) {
            $params = array_merge($params, $this->config['setting']);
        }

        return Yii::createObject($params);
    }

    /**
     * @return string
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getTransporter()
    {
        $protocol = $this->getProtocolObject();

        return $protocol->getTransporter($this->protocol);
    }

    /**
     * @return TransporterInterface|JsonRpcHttpTransporter|JsonRpcTransporter|JsonRpcPoolTransporter
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getTransporterObject()
    {
        return Yii::createObject($this->getTransporter(), [
            $this->config
        ]);
    }

    /**
     * @param $data
     * @return mixed|null
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function send($data)
    {
        $packer = $this->getPackerObject();
        $packedData = $packer->pack($data);

        $response = $this->getTransporterObject()->send($packedData);

        return $packer->unpack((string)$response);
    }

}