<?php

namespace ESD\Yii\PdoPlugin;


class PdoPools
{
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
     * 添加连接池
     * @param Pool $pool
     */
    public function addPool(PdoPool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}