<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Exception;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Trait GetRedis
 * @package ESD\Plugins\Redis
 */
trait GetRedis
{
    /**
     * @param string|null $name
     * @return mixed|\Redis
     * @throws \ESD\Core\Exception
     * @throws \ESD\Plugins\Redis\RedisException
     * @throws \RedisException
     */
    public function redis(?string $name = "default")
    {
        $db = getContextValue("Redis:$name");

        //Default database number
        $defaultDbNum = Server::$instance->getConfigContext()->get("redis.{$name}.database");

        if ($db == null) {
            /** @var RedisPools $redisPools */
            $redisPools = getDeepContextValueByClassName(RedisPools::class);

            $pool = $redisPools->getPool($name);

            if ($pool == null) {
                throw new Exception(Yii::t('esd', '{driverName} connection pool named {name} not found', [
                    'driverName' => 'Redis',
                    'name' => $name
                ]));
            }
            $db = $pool->db();
        }

        //Be sure to select default database
        if ($db->getDbNum() != $defaultDbNum) {
            $db->select($defaultDbNum);
        }

        return $db;
    }
}
