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
use ESD\Yii\Db\Exception;

/**
 * @property Config $config
 */
class PoolConnection extends CorePoolConnection
{
    /**
     * @var DbConnection
     */
    protected $connection;

    /**
     * @var PdoPool
     */
    protected $pool;

    /**
     * @param PdoPool $pool
     * @param Config $config
     */
    public function __construct(PdoPool $pool, Config $config)
    {
        parent::__construct($pool, $config);
    }

    /**
     * @return DbConnection
     */
    public function getConnection(): DbConnection
    {
        return $this->connection;
    }

    /**
     * @param DbConnection $connection
     * @return void
     */
    public function setConnection(DbConnection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return bool
     * @throws Exception
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
     * @throws ConnectionException|Exception
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
     * @throws Exception
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
        if ($this->connection instanceof DbConnection) {
            $this->connection->close();
        }

        unset($this->connection);

        return true;
    }

    /**
     * @return DbConnection
     * @throws ConnectionException
     * @throws Exception
     */
    public function getDbConnection(): DbConnection
    {
        try {
            $activeConnection =  $this->getActiveConnection();
            return $activeConnection->getConnection();
        } catch (\Throwable $exception) {
            Server::$instance->getLog()->warning('Get connection failed, try again. ' . $exception);

            $activeConnection =  $this->getActiveConnection();
            return $activeConnection->getConnection();
        }
    }


}
