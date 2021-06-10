<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Core\Pool;

use ESD\Core\Channel\Channel;

/**
 * Class Pool
 * @package ESD\Core\Pool
 */
class Pool
{
    /**
     * @var Channel
     */
    protected $pool;

    /** @var Config */
    protected $config;

    /**
     * @return Channel
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}