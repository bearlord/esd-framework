<?php
/**
 * ESD Yii mongodb plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Mongodb;

/**
 * Class PdoPools
 * @package ESD\Yii\Plugin\Mongodb
 */
class MongodbPools
{
    /**
     * @var array
     */
    protected $poolList = [];

    /**
     * Get pool
     *
     * @param $name
     * @return
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * @param PdoPool $pool
     */
    public function addPool(MongodbPool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}