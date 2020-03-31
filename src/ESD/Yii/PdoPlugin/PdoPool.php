<?php

namespace ESD\Yii\PdoPlugin;

use ESD\Core\Channel\Channel;
use ESD\Yii\Base\Application;
use ESD\Yii\Db\Connection;
use ESD\Yii\Yii;


class PdoPool
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var PdoOneConfig
     */
    protected $config;

    /**
     * Pool constructor.
     * @param Config $config
     * @throws \ESD\Yii\Db\Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $_config = $config->buildConfig();

        $this->pool = DIGet(Channel::class, [$config->getPoolMaxNumber()]);

        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = $this->connect($config);
            $this->pool->push($db);
        }
    }

    /**
     * Connect
     * @param Config $config
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
     * @return \ESD\Yii\Db\Connection
     * @throws \ESD\BaseServer\Exception
     */
    public function db()
    {
        $contextKey = "Pdo:{$this->getConfig()->getName()}";
        $db = getContextValue($contextKey);
        if ($db == null) {
            $db = $this->pool->pop();
            defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }

    /**
     * @return PdoOneConfig
     */
    public function getconfig()
    {
        return $this->config;
    }

    /**
     * @param PdoOneConfig $config
     */
    public function setconfig($config)
    {
        $this->config = $config;
    }

    public function getPool()
    {
        return $this->pool;
    }
}