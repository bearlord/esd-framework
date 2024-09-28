<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Server\Server;

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
    public function pdo(string $name = "default")
    {
        if ($name === "default") {
            $poolKey = "default";
            $contextKey = "Pdo:default";
        } elseif ($name === "slave") {
            $slaveConfigs = Server::$instance->getConfigContext()->get("yii.db.default.slaves");
            if (empty($slaveConfigs)) {
                $poolKey = "default";
                $contextKey = "Pdo:default";
            } else {
                $slaveRandKey = array_rand($slaveConfigs);

                $poolKey = sprintf("default.slave.%s", $slaveRandKey);
                $contextKey = sprintf("Pdo:default.slave.%s", $slaveRandKey);
            }

        } elseif ($name === "master") {
            $masterConfigs = Server::$instance->getConfigContext()->get("yii.db.default.masters");
            if (empty($masterConfigs)) {
                $poolKey = "default";
                $contextKey = "Pdo:default";
            } else {
                $masterRandKey = array_rand($masterConfigs);

                $poolKey = sprintf("default.master.%s", $masterRandKey);
                $contextKey = sprintf("Pdo:default.master.%s", $masterRandKey);
            }
        }

        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var \ESD\Yii\Plugin\Pdo\PdoPools $pdoPools * */
            $pdoPools = getDeepContextValueByClassName(PdoPools::class);
            /** @var \ESD\Yii\Plugin\Pdo\PdoPool $pool */
            $pool = $pdoPools->getPool($poolKey);
            if ($pool == null) {
                throw new \PDOException("No Pdo connection pool named {$poolKey} was found");
            }
            return $pool->db();
        } else {
            return $db;
        }
    }
}
