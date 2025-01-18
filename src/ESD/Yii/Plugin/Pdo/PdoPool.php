<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Pool\ConnectionInterface;
use ESD\Core\Pool\Pool;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Db\Exception;

/**
 * Class PdoPool
 * @package ESD\Yii\Plugin\Pdo
 */
class PdoPool extends Pool
{
    /**
     * @var \ESD\Core\Channel\Channel
     */
    protected $pool;

    /**
     * @var \ESD\Yii\Plugin\Pdo\Config
     */
    protected $config;

    /**
     * @var \ESD\Core\Channel\Channel
     */
    protected $channel;

    /**
     * @param \ESD\Yii\Plugin\Pdo\Config $config
     * @throws \ESD\Yii\Db\Exception
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * @return \ESD\Core\Pool\ConnectionInterface
     */
    protected function createConnection(): ConnectionInterface
    {
        $connection = new Connection($this, $this->getConfig());
        $connection->connect();

        return $connection;
    }

    /**
     * @return \ESD\Yii\Db\Connection
     * @throws \Exception
     */
    public function db(): \ESD\Yii\Db\Connection
    {
        $contextKey = sprintf("Pdo:%s", $this->getConfig()->getName());

        $db = getContextValue($contextKey);
        if ($db == null) {
            /** @var \ESD\Yii\Plugin\Pdo\Connection $poolConnection */
            $poolConnection = $this->get();
            if (empty($poolConnection)) {
                $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase maxConnections";
                Server::$instance->getLog()->error($errorMessage);
                throw new \RuntimeException($errorMessage);
            }

            /** @var \ESD\Yii\Db\Connection $db */
            $db = $poolConnection->getDbConnection();

            \Swoole\Coroutine::defer(function () use ($poolConnection, $contextKey) {
                $db = getContextValue($contextKey);

                $poolConnection->setLastUseTime(microtime(true));
                $poolConnection->setConnection($db);

                $this->release($poolConnection);
            });
            setContextValue($contextKey, $db);
        }

        if (! $db instanceof \ESD\Yii\Db\Connection) {
            $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase maxConnections";
            Server::$instance->getLog()->error($errorMessage);
            throw new \RuntimeException($errorMessage);
        }

        return $db;
    }

}
