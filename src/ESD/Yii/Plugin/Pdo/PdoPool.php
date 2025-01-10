<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Pool\Pool;
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
     * @var \ESD\Core\Channel\Channel
     */
    protected $pool;

    /**
     * @var \ESD\Yii\Plugin\Pdo\Config
     */
    protected $config;

    /**
     * @var \ESD\Yii\Plugin\Pdo\ChannelImpl
     */
    protected $channel;

    /**
     * Pool constructor.
     * @param Config $config
     * @throws \ESD\Yii\Db\Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->channel = new ChannelImpl($config->getPoolMaxNumber());

        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = $this->connect($config);
            $this->channel->push($db);
        }
    }

    /**
     * @param \ESD\Yii\Plugin\Pdo\Config $config
     * @return \ESD\Yii\Db\Connection
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
     * @throws \Exception
     */
    public function db(): Connection
    {
        $contextKey = sprintf("Pdo:%s", $this->getConfig()->getName());

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var Connection $db */
            $db = $this->channel->pop();
            if ($db == null) {
                $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase poolMaxNumber";
                Server::$instance->getLog()->error($errorMessage);
                throw new \RuntimeException($errorMessage);
            }

            \Swoole\Coroutine::defer(function () use ($contextKey) {
                $db = getContextValue($contextKey);
                $this->channel->push($db);
            });
            setContextValue($contextKey, $db);
        }

        if (! $db instanceof Connection) {
            $errorMessage = "Connection pool {$contextKey} exhausted, Cannot establish new connection, please increase poolMaxNumber";
            Server::$instance->getLog()->error($errorMessage);
            throw new \RuntimeException($errorMessage);
        }

        return $db;
    }
}
