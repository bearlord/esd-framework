<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Channel\Channel;
use ESD\Core\Pool\Pool;
use ESD\Coroutine\Coroutine;
use ESD\Yii\Db\Connection;

/**
 * Class PdoPool
 * @package ESD\Yii\Plugin\Pdo
 */
class PdoPool extends Pool
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
     * @param Config $config
     * @return Connection
     * @throws \ESD\Yii\Db\Exception
     */
    protected function connect(Config $config)
    {
        $db = new Connection();
        $db->poolName = $config->getName();
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
        $contextKey = sprintf("Pdo:%s", $this->getConfig()->getName());

        $db = getContextValue($contextKey);
 
        if ($db == null) {
            /** @var Connection $db */
            $db = $this->pool->pop();
            
            \Swoole\Coroutine::defer(function () use ($contextKey) {
                $db = getContextValue($contextKey);
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }
}