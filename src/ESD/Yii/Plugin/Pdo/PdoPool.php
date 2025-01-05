<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Channel\Channel;
use ESD\Core\Pool\Pool;
use ESD\Coroutine\Coroutine;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Db\Connection;
use ESD\Yii\Db\Exception;

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

    /** @var Config */
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
    protected function connect(Config $config): Connection
    {
        try {
            $db = new Connection([
                "poolName" => $config->getName(),
                "dsn" => $config->getDsn(),
                "username" => $config->getUsername(),
                "password" => $config->getPassword(),
                "charset" => $config->getCharset(),
                "tablePrefix" => $config->getTablePrefix(),
                "enableSchemaCache" => $config->getEnableSchemaCache(),
                "schemaCacheDuration" => $config->getSchemaCacheDuration(),
                "schemaCache" => $config->getSchemaCache(),
            ]);
            $db->open();
        } catch (Exception $e) {
            Server::$instance->getLog()->error($e->getMessage());

            throw new Exception($e->getMessage(), $e->errorInfo, (int)$e->getCode(), $e);
        }

        return $db;
    }

    /**
     * @return \ESD\Yii\Db\Connection
     */
    public function db(): Connection
    {
        $contextKey = sprintf("Pdo:%s", $this->getConfig()->getName());

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var Connection $db */
            $db = $this->pool->pop();
            if ($db == null) {
                Server::$instance->getLog()->error("Couldn't pop item from {$contextKey} database pool, please increase poolMaxNumber");
                throw new \PDOException("Couldn't pop item from {$contextKey} database pool, please increase poolMaxNumber");
            }

            \Swoole\Coroutine::defer(function () use ($contextKey) {
                $db = getContextValue($contextKey);
                $this->pool->push($db);
            });
            setContextValue($contextKey, $db);
        }
        return $db;
    }
}
