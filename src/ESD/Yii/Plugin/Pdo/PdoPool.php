<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Channel\Channel;
use ESD\Coroutine\Coroutine;
use ESD\Yii\Db\Connection;

/**
 * Class PdoPool
 * @package ESD\Yii\Plugin\Pdo
 */
class PdoPool
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
     * @throws \ESD\Yii\Db\Exception
     */
    protected function connect($config)
    {
        $db = new Connection();
        $db->dsn = $config->getDsn();
        $db->username = $config->getUsername();
        $db->password = $config->getPassword();
        $db->charset = $config->getCharset();
        $db->tablePrefix = $config->getTablePrefix();
        $db->enableSchemaCache = $config->getEnableSchemaCache();
        $db->schemaCacheDuration = $config->getSchemaCacheDuration();
        $db->schemaCache = $config->getSchemaCache();
        $db->open();
        return $db;
    }

    /**
     * @return mixed
     */
    public function db()
    {
        $contextKey = "Pdo:{$this->getConfig()->getName()}";
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