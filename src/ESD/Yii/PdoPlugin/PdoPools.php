<?php
/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\PdoPlugin;

/**
 * Class PdoPools
 * @package ESD\Yii\PdoPlugin
 */
class PdoPools
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
    public function addPool(PdoPool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}