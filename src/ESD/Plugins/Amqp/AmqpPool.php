<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Coroutine\Coroutine;

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
     * @return \ESD\Yii\Queue\Drivers\Amqp\Context
     * @throws \Exception
     */
    protected function connect($config)
    {
        return new \ESD\Yii\Queue\Drivers\Amqp\Context([
            'connection' => (new Connection($config))->getConnection()
        ]);
    }

    /**
     * @return \ESD\Yii\Queue\Drivers\Amqp\Context
     */
    public function db()
    {
        $contextKey = "Amqp:{$this->getConfig()->getName()}";
        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var \ESD\Yii\Queue\Drivers\Amqp\Context $db */
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