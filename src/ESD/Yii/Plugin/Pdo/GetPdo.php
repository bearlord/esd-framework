<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Server\Coroutine\Server;
use ESD\Yii\Db\Connection;
use ESD\Yii\Yii;

/**
 * Trait GetPdo
 * @package ESD\Yii\Plugin\Pdo]
 */
trait GetPdo
{
    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function pdo(?string $name = "default")
    {
        $subname = "";
        if (strpos($name, ".") > 0) {
            list($name, $subname) = explode(".", $name, 2);
        }

        switch ($subname) {
            case "slave":
            case "master":
                $_configKey = sprintf("yii.db.%s.%ss", $name, $subname);
                $_configs = Server::$instance->getConfigContext()->get($_configKey);
                if (empty($_configs)) {
                    $poolKey = $name;
                    $contextKey = sprintf("Pdo:%s", $name);
                } else {
                    $_randKey = array_rand($_configs);

                    $poolKey = sprintf("%s.%s.%s", $name, $subname, $_randKey);
                    $contextKey = sprintf("Pdo:{$name}%s.%s.%s", $name, $subname, $_randKey);
                }
                break;

            default:
                $poolKey = $name;
                $contextKey = sprintf("Pdo:%s", $name);
                break;
        }

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var PdoPools $pdoPools */
            $pdoPools = getDeepContextValueByClassName(PdoPools::class);
            if (!empty($pdoPools)) {
                /** @var \ESD\Yii\Plugin\Pdo\PdoPool $pool */
                $pool = $pdoPools->getPool($poolKey);
                if ($pool == null) {
                    Server::$instance->getLog()->error("No Pdo connection pool named {$poolKey} was found");
                    throw new \PDOException("No Pdo connection pool named {$poolKey} was found");
                }
                try {
                    $db = $pool->db();
                    if (empty($db)) {
                        Server::$instance->getLog()->error("Empty db, get db once.");
                        return $this->getDbOnce($name);
                    }
                    return $db;
                } catch (\Exception $e) {
                    Server::$instance->getLog()->error($e);
                }
            } else {
                return $this->getDbOnce($name);
            }
        } else {
            return $db;
        }
    }

    /**
     * Get db once
     * @return Connection|object|null
     * @throws \ESD\Yii\Db\Exception|\ESD\Yii\Base\InvalidConfigException
     */
    public function getDbOnce($name): ?Connection
    {
        $contextKey = sprintf("Pdo:%s", $name);
        $db = getContextValue($contextKey);
        if (!empty($db)) {
            return $db;
        }

        $_configKey = sprintf("yii.db.%s", $name);
        $_config = Server::$instance->getConfigContext()->get($_configKey);
        $db = new Connection([
            'poolName' => $name,
            'dsn' => $_config['dsn'],
            'username' => $_config['username'],
            'password' => $_config['password'],
            'charset' => $_config['charset'] ?? 'utf8',
            'tablePrefix' => $_config['tablePrefix'],
            'enableSchemaCache' => $_config['enableSchemaCache'],
            'schemaCacheDuration' => $_config['schemaCacheDuration'],
            'schemaCache' => $_config['schemaCache'],
        ]);
        $db->open();
        setContextValue($contextKey, $db);

        return $db;
    }
}
