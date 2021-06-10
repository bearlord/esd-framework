<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Core\Pool;

/**
 * Class Configs
 * @package ESD\Core\Pool
 */
class Configs
{
    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    /**
     * @param Config $config
     */
    public function addConfig(Config $config)
    {
        $this->configs[$config->getName()] = $config;
    }
}