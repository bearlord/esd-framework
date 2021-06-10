<?php

/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Core\Pool;

/**
 * Class Pools
 * @package ESD\Core\Pool
 */
class Pools
{
    /**
     * @var array
     */
    protected $poolList = [];

    /**
     * @return array
     */
    public function getPoolList(): array
    {
        return $this->poolList;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * @param Pool $pool
     */
    public function addPool(Pool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}