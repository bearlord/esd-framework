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

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var \ESD\Core\Pool\Config
     */
    protected $config;

    /**
     * @var \ESD\Core\Pool\PoolOption
     */
    protected $option;


    /**
     * @param \ESD\Core\Pool\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->initOption($config->toConfigArray());

        $this->generateChannel();
    }

    /**
     * @return Channel
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @return \ESD\Core\Channel\Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param \ESD\Core\Channel\Channel $channel
     * @return void
     */
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return void
     */
    protected function generateChannel()
    {
        $channel = DIGet(Channel::class, [$this->option->getMaxConnections()]);

        $this->setChannel($channel);
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $options
     * @return void
     */
    protected function initOption(array $options = [])
    {
        $this->option = new PoolOption(
            $options['minConnections'] ?? null,
            $options['maxConnections'] ?? null,
            $options['connectTimeout'] ?? null,
            $options['waitTimeout'] ?? null,
            $options['heartbeat'] ?? null,
            $options['maxIdleTime'] ?? null
        );
    }

}
