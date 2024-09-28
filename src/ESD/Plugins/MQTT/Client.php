<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT;

use ESD\Plugins\MQTT\Config\ClientConfig;
use ESD\Plugins\MQTT\Exception\ConnectException;
use ESD\Plugins\MQTT\Exception\ProtocolException;
use ESD\Plugins\MQTT\Hex\ReasonCode;
use ESD\Plugins\MQTT\Protocol\ProtocolV3;
use ESD\Plugins\MQTT\Protocol\ProtocolV5;

/**
 * Class Client
 * @package ESD\Plugins\MQTT;
 */
class Client
{
    /** @var \Swoole\Coroutine\Client|\Swoole\Client */
    private $client;

    private $messageId = 0;

    private $connectData = [];

    private $host;

    private $port;

    private $config;

    private $clientType;

    public const COROUTINE_CLIENT_TYPE = 1;

    public const SYNC_CLIENT_TYPE = 2;

    /**
     * @param string $host
     * @param int $port
     * @param ClientConfig $config
     * @param int $clientType
     */
    public function __construct(
        string $host,
        int $port,
        ClientConfig $config,
        int $clientType = self::COROUTINE_CLIENT_TYPE
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->config = $config;
        $this->clientType = $clientType;

        if ($this->isCoroutineClientType()) {
            $this->client = new \Swoole\Coroutine\Client($config->getSockType());
        } else {
            $this->client = new \Swoole\Client($config->getSockType());
        }

        $this->client->set($config->getSwooleConfig());
        if (!$this->client->connect($host, $port)) {
            $this->reConnect();
        }
    }

    /**
     * @param bool $clean
     * @param array $will
     * @return bool
     */
    public function connect(bool $clean = true, array $will = [])
    {
        $data = [
            'type' => Protocol\Types::CONNECT,
            'protocol_name' => $this->getConfig()->getProtocolName(),
            'protocol_level' => $this->getConfig()->getProtocolLevel(),
            'clean_session' => $clean,
            'client_id' => $this->getConfig()->getClientId(),
            'keep_alive' => $this->getConfig()->getKeepAlive(),
            'properties' => $this->getConfig()->getProperties(),
            'user_name' => $this->getConfig()->getUserName(),
            'password' => $this->getConfig()->getPassword(),
        ];
        if (!empty($will)) {
            if (!isset($will['topic']) || empty($will['topic'])) {
                throw new ProtocolException('Topic cannot be empty');
            }
            $data['will'] = $will;
        }

        $this->connectData = $data;

        return $this->send($data);
    }

    /**
     * @param array $topics
     * @param array $properties
     * @return bool
     */
    public function subscribe(array $topics, array $properties = [])
    {
        $data = [
            'type' => Protocol\Types::SUBSCRIBE,
            'message_id' => $this->buildMessageId(),
            'properties' => $properties,
            'topics' => $topics,
        ];

        return $this->send($data);
    }

    /**
     * @param array $topics
     * @param array $properties
     * @return bool
     */
    public function unSubscribe(array $topics, array $properties = [])
    {
        $data = [
            'type' => Protocol\Types::UNSUBSCRIBE,
            'message_id' => $this->buildMessageId(),
            'properties' => $properties,
            'topics' => $topics,
        ];

        return $this->send($data);
    }

    /**
     * @param string $topic
     * @param string $message
     * @param int $qos
     * @param int $dup
     * @param int $retain
     * @param array $properties
     * @return bool
     */
    public function publish(
        string $topic,
        string $message,
        int $qos = 0,
        int $dup = 0,
        int $retain = 0,
        array $properties = []
    ) {
        if (empty($topic)) {
            if ($this->getConfig()->isMQTT5()) {
                if (!isset($properties['topic_alias']) || empty($properties['topic_alias'])) {
                    throw new ProtocolException('Topic cannot be empty or need to set topic_alias');
                }
            } else {
                throw new ProtocolException('Topic cannot be empty');
            }
        }

        $response = $qos > 0;

        // A PUBLISH packet MUST NOT contain a Packet Identifier if its QoS value is set to 0
        $message_id = 0;
        if ($qos) {
            $message_id = $this->buildMessageId();
        }

        return $this->send(
            [
                'type' => Protocol\Types::PUBLISH,
                'qos' => $qos,
                'dup' => $dup,
                'retain' => $retain,
                'topic' => $topic,
                'message_id' => $message_id,
                'properties' => $properties,
                'message' => $message,
            ],
            $response
        );
    }

    /**
     * @return bool
     */
    public function ping()
    {
        return $this->send(['type' => Protocol\Types::PINGREQ]);
    }

    /**
     * @param int $code
     * @param array $properties
     * @return bool
     */
    public function close(int $code = ReasonCode::NORMAL_DISCONNECTION, array $properties = []): bool
    {
        $this->send(['type' => Protocol\Types::DISCONNECT, 'code' => $code, 'properties' => $properties], false);

        return $this->client->close();
    }

    /**
     * @param int $code
     * @param array $properties
     * @return bool
     */
    public function auth(int $code = ReasonCode::SUCCESS, array $properties = [])
    {
        return $this->send(['type' => Protocol\Types::AUTH, 'code' => $code, 'properties' => $properties]);
    }

    /**
     * @return void
     */
    private function reConnect()
    {
        $result = false;
        $maxAttempts = $this->getConfig()->getMaxAttempts();
        $delay = $this->getConfig()->getDelay();
        while (!$result) {
            if ($maxAttempts === 0) {
                $this->handleException();
            }
            $this->sleep($delay);
            $this->client->close();
            $result = $this->client->connect($this->getHost(), $this->getPort());
            if ($maxAttempts > 0) {
                $maxAttempts--;
            }
        }
    }

    /**
     * @return mixed
     */
    private function handleException()
    {
        if ($this->isCoroutineClientType()) {
            $errMsg = $this->client->errMsg;
        } else {
            $errMsg = socket_strerror($this->client->errCode);
        }
        $this->client->close();
        throw new ConnectException($errMsg, $this->client->errCode);
    }

    /**
     * @param array $data
     * @param bool $response
     * @return bool
     * @throws \Throwable
     */
    public function send(array $data, bool $response = true)
    {
        if ($this->getConfig()->isMQTT5()) {
            $package = ProtocolV5::pack($data);
        } else {
            $package = ProtocolV3::pack($data);
        }

        $this->client->send($package);

        if ($response) {
            return $this->recv();
        }

        return true;
    }

    /**
     * @return array|bool
     * @throws \Throwable
     */
    public function recv()
    {
        $response = $this->getResponse();
        if ($response === '' || !$this->client->isConnected()) {
            $this->reConnect();
            $this->connect($this->getConnectData('clean_session') ?? true, $this->getConnectData('will') ?? []);
        } elseif ($response === false && $this->client->errCode !== SOCKET_ETIMEDOUT) {
            $this->handleException();
        } elseif (is_string($response) && strlen($response) > 0) {
            if ($this->getConfig()->isMQTT5()) {
                return ProtocolV5::unpack($response);
            }

            return ProtocolV3::unpack($response);
        }

        return true;
    }

    /**
     * @return bool|string
     */
    protected function getResponse()
    {
        if ($this->isCoroutineClientType()) {
            $response = $this->client->recv();
        } else {
            $write = $error = [];
            $read = [$this->client];
            $n = swoole_client_select($read, $write, $error);
            if ($n > 0) {
                $response = $this->client->recv();
            } else {
                $response = true;
            }
        }

        return $response;
    }

    /**
     * @return bool
     */
    protected function isCoroutineClientType(): bool
    {
        return $this->clientType === self::COROUTINE_CLIENT_TYPE;
    }

    /**
     * @return int
     */
    public function buildMessageId(): int
    {
        if ($this->messageId === 65535) {
            $this->messageId = 0;
        }

        return ++$this->messageId;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public static function genClientID(string $prefix = 'Simps_'): string
    {
        return uniqid($prefix);
    }

    /**
     * @param int $ms
     */
    public function sleep(int $ms): void
    {
        if ($this->isCoroutineClientType()) {
            \Swoole\Coroutine::sleep($ms / 1000);
        } else {
            usleep($ms * 1000);
        }
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return ClientConfig
     */
    public function getConfig(): ClientConfig
    {
        return $this->config;
    }

    /**
     * @param string|null $key
     * @return array|mixed|null
     */
    public function getConnectData(?string $key = null)
    {
        if ($key) {
            if (isset($this->connectData[$key])) {
                return $this->connectData[$key];
            }

            return null;
        }

        return $this->connectData;
    }

    /**
     * @return \Swoole\Client|\Swoole\Coroutine\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
