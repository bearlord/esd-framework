<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2019/10/17
 * Time: 13:47
 */

namespace ESD\Yii\PdoPlugin;


class Configs
{
    /**
     * @var PostgresqlOneConfig[]
     */
    protected $configs;

    /**
     * @return PostgresqlOneConfig[]
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @param PostgresqlOneConfig[] $configs
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }

    public function addConfig(Config $buildFromConfig)
    {
        $this->configs[$buildFromConfig->getName()] = $buildFromConfig;
    }
}