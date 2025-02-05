<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Server\Coroutine\Server;
use ESD\Core\Pool\Connection as CorePoolConnection;
use ESD\Yii\Db\Connection as DbConnection;
use ESD\Core\Pool\Exception\ConnectionException;

class PoolConnection extends CorePoolConnection
{
    /**
     * @var \ESD\Yii\Db\Connection
     */
    protected $connection;

    /**
     * @var \ESD\Yii\Plugin\Pdo\PdoPool
     */
    protected $pool;

    /**
     * @var \ESD\Yii\Plugin\Pdo\Config
     */
    protected $config;

    /**
     * @param \ESD\Yii\Plugin\Pdo\PdoPool $pool
     * @param \ESD\Yii\Plugin\Pdo\Config $config
     */
    public function __construct(PdoPool $pool, Config $config)
    {
        parent::__construct($pool, $config);
    }

    /**
     * @return \ESD\Yii\Db\Connection
     */
    public function getConnection(): DbConnection
    {
        return $this->connection;
    }

    /**
     * @param \ESD\Yii\Db\Connection $connection
     * @return void
     */
    public function setConnection(DbConnection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return \ESD\Yii\Plugin\Pdo\Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param \ESD\Yii\Plugin\Pdo\Config $config
     * @return void
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws \ESD\Yii\Db\Exception
     */
    public function connect(): bool
    {
        $connectionHandle = new DbConnection([
            "poolName" => $this->config->getName(),
            "dsn" => $this->config->getDsn(),
            "username" => $this->config->getUsername(),
            "password" => $this->config->getPassword(),
            "charset" => $this->config->getCharset(),
            "tablePrefix" => $this->config->getTablePrefix(),
            "enableSchemaCache" => $this->config->getEnableSchemaCache(),
            "schemaCacheDuration" => $this->config->getSchemaCacheDuration(),
            "schemaCache" => $this->config->getSchemaCache(),
        ]);
        $connectionHandle->open();

        $this->setConnection($connectionHandle);
        $this->setLastUseTime(microtime(true));

        return true;
    }

    /**
     * @return $this
     * @throws \ESD\Yii\Plugin\Pdo\ConnectionException
     */
    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        $this->close();
        $this->connect();

        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if ($this->connection instanceof \ESD\Yii\Db\Connection) {
            $this->connection->close();
        }

        unset($this->connection);

        return true;
    }

    /**
     * @return \ESD\Yii\Db\Connection
     * @throws \ESD\Yii\Plugin\Pdo\ConnectionException
     */
    public function getDbConnection(): DbConnection
    {
        try {
            $activeConnection =  $this->getActiveConnection();
            $connection = $activeConnection->getConnection();
            return $connection;
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->warning('Get connection failed, try again. ' . (string)$exception);

            $activeConnection =  $this->getActiveConnection();
            return $activeConnection->getConnection();
        }
    }


}
