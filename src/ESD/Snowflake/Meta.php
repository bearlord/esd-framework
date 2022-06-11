<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Snowflake;

/**
 * Class Meta
 * @package ESD\Snowflake
 */
class Meta
{
    /**
     * @var int [0, 31]
     */
    protected $dataCenterId;

    /**
     * @var int [0, 31]
     */
    protected $workerId;

    /**
     * @var int [0, 4095]
     */
    protected $sequence;

    /**
     * @var int seconds or milliseconds
     */
    protected $timestamp = 0;

    /**
     * @var int seconds or milliseconds
     */
    protected $beginTimestamp = 0;

    /**
     * @param int $dataCenterId
     * @param int $workerId
     * @param int $sequence
     * @param int $timestamp
     * @param int $beginTimestamp
     */
    public function __construct(int $dataCenterId, int $workerId, int $sequence, int $timestamp, int $beginTimestamp = 0)
    {
        $this->dataCenterId = $dataCenterId;
        $this->workerId = $workerId;
        $this->sequence = $sequence;
        $this->timestamp = $timestamp;
        $this->beginTimestamp = $beginTimestamp;
    }

    /**
     * @return int
     */
    public function getDataCenterId(): int
    {
        return $this->dataCenterId;
    }

    /**
     * @param int $dataCenterId
     */
    public function setDataCenterId(int $dataCenterId): void
    {
        $this->dataCenterId = $dataCenterId;
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @param int $workerId
     */
    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @param int $sequence
     */
    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getBeginTimestamp(): int
    {
        return $this->beginTimestamp;
    }

    /**
     * @param int $beginTimestamp
     */
    public function setBeginTimestamp(int $beginTimestamp): void
    {
        $this->beginTimestamp = $beginTimestamp;
    }

    /**
     * @return int
     */
    public function getTimeInterval(): int
    {
        return $this->timestamp - $this->beginTimestamp;
    }

}
