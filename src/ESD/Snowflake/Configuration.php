<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Snowflake;

/**
 * Class Configuration
 * @package ESD\Snowflake
 */
class Configuration implements ConfigurationInterface
{
    protected $millisecondBits = 41;

    protected $dataCenterIdBits = 5;

    protected $workerIdBits = 5;

    protected $sequenceBits = 12;

    /**
     * @return int
     */
    public function maxWorkerId(): int
    {
        return -1 ^ (-1 << $this->workerIdBits);
    }

    /**
     * @return int
     */
    public function maxDataCenterId(): int
    {
        return -1 ^ (-1 << $this->dataCenterIdBits);
    }

    /**
     * @return int
     */
    public function maxSequence(): int
    {
        return -1 ^ (-1 << $this->sequenceBits);
    }

    /**
     * @return int
     */
    public function getTimestampLeftShift(): int
    {
        return $this->sequenceBits + $this->workerIdBits + $this->dataCenterIdBits;
    }

    /**
     * @return int
     */
    public function getDataCenterIdShift(): int
    {
        return $this->sequenceBits + $this->workerIdBits;
    }

    /**
     * @return int
     */
    public function getWorkerIdShift(): int
    {
        return $this->sequenceBits;
    }

    /**
     * @return int
     */
    public function getTimestampBits(): int
    {
        return $this->millisecondBits;
    }

    /**
     * @return int
     */
    public function getDataCenterIdBits(): int
    {
        return $this->dataCenterIdBits;
    }

    /**
     * @return int
     */
    public function getWorkerIdBits(): int
    {
        return $this->workerIdBits;
    }

    /**
     * @return int
     */
    public function getSequenceBits(): int
    {
        return $this->sequenceBits;
    }
}
