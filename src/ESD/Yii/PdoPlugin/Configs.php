<?php

namespace ESD\Yii\PdoPlugin;

class Configs
{
    /**
     * @var array
     */
    protected $configs;

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
     * @param Config $buildFromConfig
     */
    public function addConfig(Config $buildFromConfig)
    {
        $this->configs[$buildFromConfig->getName()] = $buildFromConfig;
    }
}