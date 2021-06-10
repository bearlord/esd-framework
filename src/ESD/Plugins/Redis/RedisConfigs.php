<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

/**
 * Class RedisConfig
 * @package ESD\Plugins\Redis
 */
class RedisConfigs
{
    /**
     * @var RedisOneConfig[]
     */
    protected $redisConfigs;

    /**
     * @return RedisOneConfig[]
     */
    public function getRedisConfigs(): array
    {
        return $this->redisConfigs;
    }

    /**
     * @param RedisOneConfig[] $redisConfigs
     */
    public function setRedisConfigs(array $redisConfigs): void
    {
        $this->redisConfigs = $redisConfigs;
    }

    public function addRedisOneConfig(RedisOneConfig $buildFromConfig)
    {
        $this->redisConfigs[$buildFromConfig->getName()] = $buildFromConfig;
    }
}