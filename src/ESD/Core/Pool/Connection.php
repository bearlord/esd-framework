<?php

namespace ESD\Core\Pool;

use ESD\Server\Coroutine\Server;

abstract class Connection implements ConnectionInterface
{
    /**
     * @var float
     */
    protected $lastUseTime = 0.0;

    /**
     * @var float
     */
    protected $lastReleaseTime = 0.0;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var \ESD\Core\Pool\Config
     */
    protected $config;

    /**
     * @param PoolInterface $pool
     * @param Config $config
     */
    public function __construct(PoolInterface $pool, Config $config)
    {
        $this->pool = $pool;
        $this->config = $config;
    }

    /**
     * @return PoolInterface
     */
    public function getPool(): PoolInterface
    {
        return $this->pool;
    }

    /**
     * @param PoolInterface $pool
     * @return void
     */
    public function setPool(PoolInterface $pool): void
    {
        $this->pool = $pool;
    }

    /**
     * @return \ESD\Core\Pool\Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param \ESD\Core\Pool\Config $config
     * @return void
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return float
     */
    public function getLastUseTime(): float
    {
        return $this->lastUseTime;
    }

    /**
     * @param float $lastUseTime
     * @return void
     */
    public function setLastUseTime(float $lastUseTime): void
    {
        $this->lastUseTime = $lastUseTime;
    }

    /**
     * @return float
     */
    public function getLastReleaseTime(): float
    {
        return $this->lastReleaseTime;
    }

    /**
     * @param float $lastReleaseTime
     * @return void
     */
    public function setLastReleaseTime(float $lastReleaseTime): void
    {
        $this->lastReleaseTime = $lastReleaseTime;
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function getConnection()
    {
        try {
            return $this->getActiveConnection();
        } catch (\Exception $exception) {
            Server::$instance->getLog()->warning('Get connection failed, try again. ' . $exception);

            return $this->getActiveConnection();
        }
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        $maxIdleTime = $this->pool->getOption()->getMaxIdleTime();
        $now = microtime(true);

        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->setLastReleaseTime($now);
        return true;
    }

    /**
     * @return void
     */
    public function release(): void
    {
        $now = microtime(true);
        $this->setlastReleaseTime($now);

        $this->pool->release($this);
    }

    abstract public function getActiveConnection();
}
