<?php
/**
 * ESD Yii mongodb plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Mongodb;

use ESD\Core\Channel\Channel;
use ESD\Coroutine\Coroutine;
use ESD\Yii\Mongodb\Connection;

/**
 * Class PdoPool
 * @package ESD\Yii\Plugin\Mongodb
 */
class MongodbPool
{
    /**
     * @var Channel
     */
    protected $pool;

    /** @var Config  */
    protected $config;

    /**
     * Pool constructor.
     * @param Config $config
     * @throws \ESD\Yii\Db\Exception
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
     * @throws \ESD\Yii\Mongodb\Exception
     */
    protected function connect(Config $config)
    {
        $db = new Connection();
        $db->dsn = $config->getDsn();
        $db->options = $config->getOptions();
        $db->tablePrefix = $config->getTablePrefix();
        $db->open();
        return $db;
    }

    /**
     * @return mixed
     */
    public function db()
    {
        $contextKey = "Mongodb:{$this->getConfig()->getName()}";
        $db = getContextValue($contextKey);
 
        if ($db == null) {
            /** @var Connection $db */
            $db = $this->pool->pop();

            \Swoole\Coroutine::defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }

    /**
     * @return PdoOneConfig
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