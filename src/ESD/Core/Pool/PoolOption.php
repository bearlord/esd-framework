<?php

namespace ESD\Core\Pool;

class PoolOption implements PoolOptionInterface
{
    /**
     * @var int Min connections of pool.
     * This means the pool will create $minConnections connections when
     * pool initialization.
     */
    protected $minConnections = 1;

    /**
     * @var int Max connections of pool.
     *
     */
    protected $maxConnections = 10;

    /**
     * @var float The timeout of connect the connection.
     * Default value is 10 seconds.
     */
    protected $connectTimeout = 10.0;

    /**
     * @var float The timeout of pop a connection.
     *
     * Default value is 3 seconds.
     */
    protected $waitTimeout = 3.0;

    /**
     * @var int Heartbeat of connection.
     *
     * If the value is 10, then means 10 seconds.
     * If the value is -1, then means does not need the heartbeat.
     * Default value is -1.
     */
    protected $heartbeat = -1;

    /**
     * @var int The max idle time for connection.
     */
    protected $maxIdleTime = 60;

    /**
     * @param int $minConnections
     * @param int $maxConnections
     * @param float $connectTimeout
     * @param float $waitTimeout
     * @param int $heartbeat
     * @param int $maxIdleTime
     */
    public function __construct(
        ?int $minConnections = 1,
        ?int $maxConnections = 10,
        ?float $connectTimeout = 10.0,
        ?float $waitTimeout = 3.0,
        ?int $heartbeat = -1,
        ?int $maxIdleTime = 60
    )
    {
        $this->setMinConnections($minConnections);
        $this->setMaxConnections($maxConnections);
        $this->setConnectTimeout($connectTimeout);
        $this->setWaitTimeout($waitTimeout);
        $this->setHeartbeat($heartbeat);
        $this->setMaxIdleTime($maxIdleTime);
    }

    /**
     * @return int
     */
    public function getMinConnections(): int
    {
        return $this->minConnections;
    }

    /**
     * @param int $minConnections
     * @return void
     */
    public function setMinConnections(?int $minConnections): void
    {
        if (empty($minConnections)) {
            return;
        }
        $this->minConnections = $minConnections;
    }

    /**
     * @return int
     */
    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    /**
     * @param int $maxConnections
     * @return void
     */
    public function setMaxConnections(?int $maxConnections): void
    {
        if (empty($maxConnections)) {
            return;
        }
        $this->maxConnections = $maxConnections;
    }

    /**
     * @return float
     */
    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    /**
     * @param float $connectTimeout
     * @return void
     */
    public function setConnectTimeout(?float $connectTimeout): void
    {
        if (empty($connectTimeout)) {
            return;
        }
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * @return float
     */
    public function getWaitTimeout(): float
    {
        return $this->waitTimeout;
    }

    /**
     * @param float $waitTimeout
     * @return void
     */
    public function setWaitTimeout(?float $waitTimeout): void
    {
        if (empty($waitTimeout)) {
            return;
        }
        $this->waitTimeout = $waitTimeout;
    }

    /**
     * @return int
     */
    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    /**
     * @param int $heartbeat
     * @return void
     */
    public function setHeartbeat(?int $heartbeat): void
    {
        if (empty($heartbeat)) {
            return;
        }
        $this->heartbeat = $heartbeat;
    }

    /**
     * @return int
     */
    public function getMaxIdleTime(): int
    {
        return $this->maxIdleTime;
    }

    /**
     * @param int $maxIdleTime
     * @return void
     */
    public function setMaxIdleTime(?int $maxIdleTime): void
    {
        if (empty($maxIdleTime)) {
            return;
        }
        $this->maxIdleTime = $maxIdleTime;
    }
}
