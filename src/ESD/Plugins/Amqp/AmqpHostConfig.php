<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Yii\Yii;

/**
 * Class AmqpHostConfig
 * @package ESD\Plugins\Amqp
 */
class AmqpHostConfig extends BaseConfig
{
    const key = "amqp.hosts";

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $vhost;

    /**
     * ConsulConfig constructor.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }


    /**
     * Build config
     * @throws AmqpException
     */
    public function buildConfig()
    {
        if(empty($this->host)){
            throw new AmqpException(Yii::t('esd', 'Amqp host must be set'));
        }

        if(empty($this->port) || $this->port > 65535 || $this->port < 1){
            throw new AmqpException(Yii::t('esd', 'Amqp port must be set'));
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
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
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
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getVhost(): string
    {
        return $this->vhost;
    }

    /**
     * @param string $vhost
     */
    public function setVhost(string $vhost): void
    {
        $this->vhost = $vhost;
    }

}