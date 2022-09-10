<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\CircuitBreaker;

use ESD\Core\Plugins\Config\BaseConfig;

class CircuitBreakerConfig extends BaseConfig
{
    const key = "circuit_breaker";

    /**
     * @var string
     */
    protected $redisDb = "default";

    /**
     * @var bool
     */
    protected $enable = true;

    /**
     * @var int
     */
    protected $timeWindow = 30;

    /**
     * @var int
     */
    protected $failureRateThreshold = 50;

    /**
     * @var int
     */
    protected $minimumRequests = 10;

    /**
     * @var int
     */
    protected $intervalToHalfOpen = 5;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getRedisDb(): string
    {
        return $this->redisDb;
    }

    /**
     * @inheritDoc
     * @param string $redisDb
     */
    public function setRedisDb(string $redisDb): void
    {
        $this->redisDb = $redisDb;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getTimeWindow(): int
    {
        return $this->timeWindow;
    }

    /**
     * @inheritDoc
     * @param int $timeWindow
     */
    public function setTimeWindow(int $timeWindow): void
    {
        $this->timeWindow = $timeWindow;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getFailureRateThreshold(): int
    {
        return $this->failureRateThreshold;
    }

    /**
     * @inheritDoc
     * @param int $failureRateThreshold
     */
    public function setFailureRateThreshold(int $failureRateThreshold): void
    {
        $this->failureRateThreshold = $failureRateThreshold;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getMinimumRequests(): int
    {
        return $this->minimumRequests;
    }

    /**
     * @inheritDoc
     * @param int $minimumRequests
     */
    public function setMinimumRequests(int $minimumRequests): void
    {
        $this->minimumRequests = $minimumRequests;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function getIntervalToHalfOpen(): int
    {
        return $this->intervalToHalfOpen;
    }

    /**
     * @inheritDoc
     * @param int $intervalToHalfOpen
     */
    public function setIntervalToHalfOpen(int $intervalToHalfOpen): void
    {
        $this->intervalToHalfOpen = $intervalToHalfOpen;
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @inheritDoc
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }
}