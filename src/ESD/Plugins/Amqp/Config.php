<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>, bearload <565364226@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Yii\Yii;

/**
 * Class Config
 * @package ESD\Plugins\Amqp
 */
class Config extends BaseConfig
{
    const KEY = "amqp";

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $poolMaxNumber = 2;

    /**
     * @var string
     */
    protected $host = 'localhost';

    /**
     * @var int
     */
    protected $port = 5672;

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
    protected $vhost = '/';

    /**
     * @var bool
     */
    protected $insist = false;

    /**
     * @var string
     */
    protected $loginMethod = 'AMQPLAIN';

    /**
     * @var null
     */
    protected $loginResponse = null;

    /**
     * @var string
     */
    protected $locale = 'en_US';

    /**
     * @var float
     */
    protected $connectionTimeout = 3.0;

    /**
     * @var float
     */
    protected $readWriteTimeout = 130.0;

    /**
     * @var null
     */
    protected $context = null;

    /**
     * @var bool
     */
    protected $keepAlive = false;

    /**
     * @var int
     */
    protected $heartBeat = 60;

    /**
     * ConsulConfig constructor.
     * @throws \ReflectionException
     */
    public function __construct($name)
    {
        parent::__construct(self::KEY, true, "name");
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPoolMaxNumber(): int
    {
        return $this->poolMaxNumber;
    }

    /**
     * @param int $poolMaxNumber
     */
    public function setPoolMaxNumber(int $poolMaxNumber)
    {
        $this->poolMaxNumber = $poolMaxNumber;
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

    /**
     * @return bool
     */
    public function isInsist(): bool
    {
        return $this->insist;
    }

    /**
     * @param bool $insist
     */
    public function setInsist(bool $insist): void
    {
        $this->insist = $insist;
    }

    /**
     * @return string
     */
    public function getLoginMethod(): string
    {
        return $this->loginMethod;
    }

    /**
     * @param string $loginMethod
     */
    public function setLoginMethod(string $loginMethod): void
    {
        $this->loginMethod = $loginMethod;
    }

    /**
     * @return null
     */
    public function getLoginResponse()
    {
        return $this->loginResponse;
    }

    /**
     * @param null $loginResponse
     */
    public function setLoginResponse($loginResponse): void
    {
        $this->loginResponse = $loginResponse;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return float
     */
    public function getConnectionTimeout(): float
    {
        return $this->connectionTimeout;
    }

    /**
     * @param float $connectionTimeout
     */
    public function setConnectionTimeout(float $connectionTimeout): void
    {
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @return float
     */
    public function getReadWriteTimeout(): float
    {
        return $this->readWriteTimeout;
    }

    /**
     * @param float $readWriteTimeout
     */
    public function setReadWriteTimeout(float $readWriteTimeout): void
    {
        $this->readWriteTimeout = $readWriteTimeout;
    }

    /**
     * @return null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param null $context
     */
    public function setContext($context): void
    {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function isKeepAlive(): bool
    {
        return $this->keepAlive;
    }

    /**
     * @param bool $keepAlive
     */
    public function setKeepAlive(bool $keepAlive): void
    {
        $this->keepAlive = $keepAlive;
    }

    /**
     * @return int
     */
    public function getHeartBeat(): int
    {
        return $this->heartBeat;
    }

    /**
     * @param int $heartBeat
     */
    public function setHeartBeat(int $heartBeat): void
    {
        $this->heartBeat = $heartBeat;
    }


    /**
     * Build config
     * @throws AmqpException
     */
    public function buildConfig()
    {
        if (!extension_loaded('bcmath')) {
            throw new AmqpException(Yii::t('esd', 'Amqp requires the Bcmath PHP extension'));
        }

        if(empty($this->host)){
            throw new AmqpException(Yii::t('esd', '{name} must be set', [
                'name' => 'Amqp host'
            ]));
        }
    }
}