<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Snowflake;

use ESD\Snowflake\Exception\SnowflakeException;

/**
 * Class MetaGenerator
 * @package ESD\Snowflake
 */
abstract class MetaGenerator implements MetaGeneratorInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    protected $sequence = 0;

    protected $lastTimestamp = 0;

    protected $beginTimestamp = 0;

    abstract public function getDataCenterId(): int;

    abstract public function getWorkerId(): int;

    abstract public function getTimestamp(): int;

    abstract public function getNextTimestamp(): int;

    /**
     * @param int $beginTimestamp
     */
    public function __construct(int $beginTimestamp)
    {
        $this->configuration = $this->getOrSetConfiguration();
        $this->lastTimestamp = $this->getTimestamp();
        $this->beginTimestamp = $beginTimestamp;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @param ConfigurationInterface $configuration
     */
    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Configuration|ConfigurationInterface
     */
    protected function getOrSetConfiguration()
    {
        if (empty($this->configuration)) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }

    /**
     * @return int
     */
    public function getBeginTimestamp(): int
    {
        return $this->beginTimestamp;
    }

    /**
     * @return Meta
     */
    public function generate(): Meta
    {
        $timestamp = $this->getTimestamp();

        if ($timestamp == $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) % $this->configuration->maxSequence();
            if ($this->sequence == 0) {
                $timestamp = $this->getNextTimestamp();
            }
        } elseif ($timestamp < $this->lastTimestamp) {
            $this->clockMovedBackwards($timestamp, $this->lastTimestamp);
            $this->sequence = ($this->sequence + 1) % $this->configuration->maxSequence();
            $timestamp = $this->lastTimestamp;
        } else {
            $this->sequence = 0;
        }

        if ($timestamp < $this->beginTimestamp) {
            throw new SnowflakeException(sprintf('The beginTimestamp %d is invalid, because it smaller than timestamp %d.', $this->beginTimestamp, $timestamp));
        }

        $this->lastTimestamp = $timestamp;

        return new Meta($this->getDataCenterId(), $this->getWorkerId(), $this->sequence, $timestamp, $this->beginTimestamp);
    }

    /**
     * @param $timestamp
     * @param $lastTimestamp
     */
    protected function clockMovedBackwards($timestamp, $lastTimestamp)
    {
        throw new SnowflakeException(sprintf('Clock moved backwards. Refusing to generate id for %d milliseconds.', $lastTimestamp - $timestamp));
    }
}
