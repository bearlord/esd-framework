<?php
/**
 * ESD Yii Queue plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Queue;

/**
 * Class QueuePools
 * @package ESD\Yii\Plugin\Queue
 */
class QueuePools
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
    public function addPool(QueuePool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}