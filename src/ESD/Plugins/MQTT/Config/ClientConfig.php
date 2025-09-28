<?php
/**
 * ESD framework
 * @author Lu Fei <lufei@simps.io>
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Plugins\MQTT\Config;

use ESD\Plugins\MQTT\Protocol\ProtocolInterface;

class ClientConfig extends AbstractConfig
{
    /**
     * @var string
     */
    protected $clientId = "";

    /**
     * @var array
     */
    protected $swooleConfig = [
        "open_mqtt_protocol" => true,
    ];

    /**
     * @var array
     */
    protected $headers = [
        'Sec-Websocket-Protocol' => 'mqtt',
    ];

    /**
     * @var string
     */
    protected $userName = "";

    /**
     * @var string
     */
    protected $password = "";

    /**
     * @var int
     */
    protected $keepAlive = 0;

    /**
     * @var string
     */
    protected $protocolName = ProtocolInterface::MQTT_PROTOCOL_NAME;

    /**
     * @var int
     */
    protected $protocolLevel = ProtocolInterface::MQTT_PROTOCOL_LEVEL_3_1_1;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var int
     */
    protected $delay = 3000;

    /**
     * @var int
     */
    protected $maxAttempts = 0;

    /**
     * @var int
     */
    protected $sockType = SWOOLE_SOCK_TCP;

    /**
     * @var int
     */
    protected $verbose = MQTT_VERBOSE_NONE;

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return $this
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return array
     */
    public function getSwooleConfig(): array
    {
        return $this->swooleConfig;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setSwooleConfig(array $config): self
    {
        $this->swooleConfig = array_merge($this->swooleConfig, $config);

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return int
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    /**
     * @param int $keepAlive
     * @return $this
     */
    public function setKeepAlive(int $keepAlive): self
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocolName(): string
    {
        return $this->protocolName;
    }

    /**
     * @param string $protocolName
     * @return $this
     */
    public function setProtocolName(string $protocolName): self
    {
        $this->protocolName = $protocolName;

        return $this;
    }

    /**
     * @return int
     */
    public function getProtocolLevel(): int
    {
        if (!empty($this->getProperties()) && $this->protocolLevel !== ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0) {
            return ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0;
        }

        return $this->protocolLevel;
    }

    /**
     * @param int $protocolLevel
     * @return $this
     */
    public function setProtocolLevel(int $protocolLevel): self
    {
        $this->protocolLevel = $protocolLevel;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * @param int $ms
     * @return $this
     */
    public function setDelay(int $ms): self
    {
        $this->delay = $ms;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @param int $attempts
     * @return $this
     */
    public function setMaxAttempts(int $attempts): self
    {
        $this->maxAttempts = $attempts;

        return $this;
    }

    /**
     * @return int
     */
    public function getSockType(): int
    {
        return $this->sockType;
    }

    /**
     * @param int $sockType
     * @return $this
     */
    public function setSockType(int $sockType): self
    {
        $this->sockType = $sockType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMQTT5(): bool
    {
        return $this->getProtocolLevel() === ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0;
    }

    /**
     * @return int
     */
    public function getVerbose(): int
    {
        return $this->verbose;
    }

    /**
     * @param int $verbose
     * @return $this
     */
    public function setVerbose(int $verbose): self
    {
        $this->verbose = $verbose;

        return $this;
    }
}
