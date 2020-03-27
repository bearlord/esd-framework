<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/6/13
 * Time: 10:48
 */

namespace ESD\Plugins\MQTT;


use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Plugins\MQTT\Auth\EasyMqttAuth;
use ESD\Plugins\MQTT\Handler\NonHandler;

class MqttPluginConfig extends BaseConfig
{
    const key = "mqtt";
    /**
     * 允许匿名登录
     * @var bool
     */
    protected $allowAnonymousAccess = true;
    /**
     * 服务器发出消息的服务等级
     * @var int
     */
    protected $serverQos = 0;
    /**
     * 连接验证类
     * @var string
     */
    protected $mqttAuthClass = EasyMqttAuth::class;
    /**
     * 当useRoute被设置为true时，将不再具有mqtt的功能，topic字段将被当做路由的path处理
     * @var bool
     */
    protected $useRoute = false;

    /**
     * 当useRoute被设置为true时才有效，返回给客户端消息所使用的topic名称
     * @var string
     */
    protected $serverTopic = '$SERVER_RPC';

    /**
     * 当useRoute被设置为true时才有效，消息的解包类
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