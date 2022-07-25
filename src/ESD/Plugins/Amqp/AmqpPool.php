<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Coroutine\Coroutine;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Class PdoPool
 * @package ESD\Yii\Plugin\Pdo
 */
class AmqpPool
{
    /**
     * @var Channel
     */
    protected $pool;

    /** @var Config */
    protected $config;

    /**
     * AmqpPool constructor.
     * @param Config $config
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->pool = DIGet(Channel::class, [$config->getPoolMaxNumber()]);
        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = $this->connect($config);
            $this->pool->push($db);
        }
    }
    
    /**
     * @param $config
     * @return Connection
     * @throws \Exception
     */
    protected function connect($config)
    {
        return (new Connection($config));
    }

    /**
     * @return AbstractConnection
     */
    public function db()
    {
        $contextKey = sprintf("Amqp:%s", $this->getConfig()->getName());
        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var AbstractConnection $db */
            $db = $this->pool->pop();

            \Swoole\Coroutine::defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return Channel|mixed
     */
    public function getPool()
    {
        return $this->pool;
    }
}