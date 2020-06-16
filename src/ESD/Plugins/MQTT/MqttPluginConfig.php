<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\MQTT;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Plugins\MQTT\Auth\EasyMqttAuth;
use ESD\Plugins\MQTT\Handler\NonHandler;

/**
 * Class MqttPluginConfig
 * @package ESD\Plugins\MQTT
 */
class MqttPluginConfig extends BaseConfig
{
    const key = "mqtt";

    /**
     * Is Allowed anonymous access
     * @var bool
     */
    protected $allowAnonymousAccess = true;

    /**
     * Service qos
     * @var int
     */
    protected $serverQos = 0;

    /**
     * authorization class
     * @var string
     */
    protected $mqttAuthClass = EasyMqttAuth::class;

    /**
     * When useRoute is set to true, it will no longer have the function of mqtt,
     * and the topic field will be treated as the routing path
     * @var bool
     */
    protected $useRoute = false;

    /**
     * Only valid when useRoute is set to true, the topic name used in the message returned to the client
     * @var string
     */
    protected $serverTopic = '$SERVER_RPC';

    /**
     * Only valid when useRoute is set to true, the message unpacking class
     * @var string
     */
    protected $messageHandleClass = NonHandler::class;

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return string
     */
    public function getMqttAuthClass(): string
    {
        return $this->mqttAuthClass;
    }

    /**
     * @param string $mqttAuthClass
     */
    public function setMqttAuthClass(string $mqttAuthClass): void
    {
        $this->mqttAuthClass = $mqttAuthClass;
    }

    /**
     * @return bool
     */
    public function isAllowAnonymousAccess(): bool
    {
        return $this->allowAnonymousAccess;
    }

    /**
     * @param bool $allowAnonymousAccess
     */
    public function setAllowAnonymousAccess(bool $allowAnonymousAccess): void
    {
        $this->allowAnonymousAccess = $allowAnonymousAccess;
    }

    /**
     * @return bool
     */
    public function isUseRoute(): bool
    {
        return $this->useRoute;
    }

    /**
     * @param bool $useRoute
     */
    public function setUseRoute(bool $useRoute): void
    {
        $this->useRoute = $useRoute;
    }

    /**
     * @return string
     */
    public function getServerTopic(): string
    {
        return $this->serverTopic;
    }

    /**
     * @param string $serverTopic
     */
    public function setServerTopic(string $serverTopic): void
    {
        $this->serverTopic = $serverTopic;
    }

    /**
     * @return int
     */
    public function getServerQos(): int
    {
        return $this->serverQos;
    }

    /**
     * @param int $serverQos
     */
    public function setServerQos(int $serverQos): void
    {
        $this->serverQos = $serverQos;
    }

    /**
     * @return string
     */
    public function getMessageHandleClass(): string
    {
        return $this->messageHandleClass;
    }

    /**
     * @param string $messageHandleClass
     */
    public function setMessageHandleClass(string $messageHandleClass): void
    {
        $this->messageHandleClass = $messageHandleClass;
    }
}