<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Yii\Yii;

/**
 * Class RedisOneConfig
 * @package ESD\Plugins\Redis
 */
class Config extends \ESD\Core\Pool\Config
{
    /**
     * @var string
     */
    protected $name = "";

    /**
     * @var string
     */
    protected $host = "localhost";

    /**
     * @var int
     */
    protected $port = 6379;

    /**
     * @var mixed
     */
    protected $auth = null;

    /**
     * @var int
     */
    protected $database = 0;

    /**
     * @var float
     */
    protected $timeout = 0.0;

    /**
     * @var mixed
     */
    protected $reserved = null;

    /**
     * @var int
     */
    protected $retryInterval = 0;

    /**
     * @var float
     */
    protected $readTimeout = 0.0;

    /**
     * @var array
     */
    protected $cluster = [];

    /**
     * @var array
     */
    protected $sentinel = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var int
     */
    protected $poolMaxNumber = 10;

    /**
     * @var string
     */
    protected $password = "";



    /**
     * @return string
     */
    protected function getKey()
    {
        return 'redis';
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
     * @inheritDoc
     * @throws RedisException
     */
    public function buildConfig()
    {
        if (!extension_loaded('redis')) {
            throw new RedisException(Yii::t('esd', 'Redis extension is not loaded'));
        }
        if (empty($this->name)) {
            throw new RedisException(Yii::t('esd', 'Redis name must be set'));
        }
        if (empty($this->host)) {
            throw new RedisException(Yii::t('esd', 'Redis host must be set'));
        }

        return [
            "name" => $this->name,
            "host" => $this->host,
            "port" => $this->port,
            "auth" => $this->auth,
            "database" => $this->database,
            "timeout" => $this->timeout,
            "reserved" => $this->reserved,
            "retryInterval" => $this->retryInterval,
            "readTimeout" => $this->readTimeout,
            "cluster" => $this->cluster,
            "sentinel" => $this->sentinel,
            "option" => $this->options
        ];
    }

    /**
     * @return int|null
     */
    public function getPort()
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
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->database;
    }

    /**
     * @param int $database
     */
    public function setDatabase(int $database): void
    {
        $this->database = $database;
    }
}
