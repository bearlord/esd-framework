<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Exception;
use ESD\Yii\Yii;

/**
 * Trait GetRedis
 * @package ESD\Plugins\Redis
 */
trait GetRedis
{
    /**
     * @param string $name
     * @return mixed|\Redis
     * @throws RedisException
     */
    public function redis($name = "default")
    {
        $db = getContextValue("Redis:$name");
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
            return $pool->db();
        } else {
            return $db;
        }
    }
}