<?php
/**
 * ESD Yii Mongodb plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Mongodb;

use ESD\Core\Server\Server;
use ESD\Yii\Plugin\Mongodb\MongodbPool;

/**
 * Trait GetPdo
 * @package ESD\Yii\Plugin\Mongodb
 */
trait GetMongodb
{
    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function mongodb($name = "default")
    {
        if ($name === "default") {
            $poolKey = "default";
            $contextKey = "Pdo:default";
        }
        $db = getContextValue($contextKey);

        if ($db == null) {
            /** @var MongodbPools $pdoPools * */
            $pdoPools = getDeepContextValueByClassName(MongodbPools::class);
            /** @var MongodbPool $pool */
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