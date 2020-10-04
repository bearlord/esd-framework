<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

/**
 * Class AmqpPools
 * @package ESD\Plugins\Amqp
 */
class AmqpPools
{
    /**
     * @var array
     */
    protected $poolList = [];

    /**
     * Get pool
     *
     * @param string $name
     * @return AmqpPool
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * @param AmqpPool $pool
     */
    public function addPool(AmqpPool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }
}