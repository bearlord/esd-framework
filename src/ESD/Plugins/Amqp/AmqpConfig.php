<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

class AmqpConfig
{
    /**
     * @var AmqpPoolConfig[]
     */
    protected $amqpConfigs;


    public function addAmqpPoolConfig(AmqpPoolConfig $buildFromConfig)
    {
        $this->amqpConfigs[$buildFromConfig->getName()] = $buildFromConfig;
    }

    /**
     * @return AmqpPoolConfig[]
     */
    public function getAmqpConfigs(): array
    {
        return $this->amqpConfigs;
    }

    /**
     * @param AmqpPoolConfig[] $amqpConfigs
     */
    public function setAmqpConfigs(array $amqpConfigs): void
    {
        $this->amqpConfigs = $amqpConfigs;
    }
}