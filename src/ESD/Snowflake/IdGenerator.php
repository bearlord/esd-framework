<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Snowflake;

use ESD\Core\Pool\Config;
use ESD\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;

/**
 * Class IdGenerator
 * @package ESD\Snowflake;
 */
class IdGenerator implements IdGeneratorInterface
{
    /**
     * @var MetaGeneratorInterface
     */
    protected $metaGenerator;

    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * IdGenerator constructor
     */
    public function __construct(MetaGeneratorInterface $metaGenerator)
    {
        $this->metaGenerator = $metaGenerator;
        $this->config = $metaGenerator->getConfiguration();
    }

    /**
     * @return MetaGeneratorInterface
     */
    public function getMetaGenerator(): MetaGeneratorInterface
    {
        return $this->metaGenerator;
    }

    /**
     * @param MetaGeneratorInterface $metaGenerator
     */
    public function setMetaGenerator(MetaGeneratorInterface $metaGenerator): void
    {
        $this->metaGenerator = $metaGenerator;
    }


    /**
     * @param Meta|null $meta
     * @return int
     */
    public function generate(?Meta $meta = null): int
    {
        $meta = $this->getMeta($meta);

        $interval = $meta->getTimeInterval() << $this->config->getTimestampLeftShift();
        $dataCenterId = $meta->getDataCenterId() << $this->config->getDataCenterIdShift();
        $workerId = $meta->getWorkerId() << $this->config->getWorkerIdShift();

        return $interval | $dataCenterId | $workerId | $meta->getSequence();
    }

    /**
     * @param int $id
     * @return Meta
     */
    public function degenerate(int $id): Meta
    {
        $interval = $id >> $this->config->getTimestampLeftShift();
        $dataCenterId = $id >> $this->config->getDataCenterIdShift();
        $workerId = $id >> $this->config->getWorkerIdShift();

        return new Meta(
            $interval << $this->config->getDataCenterIdBits() ^ $dataCenterId,
            $dataCenterId << $this->config->getWorkerIdBits() ^ $workerId,
            $workerId << $this->config->getSequenceBits() ^ $id,
            $interval + $this->metaGenerator->getBeginTimestamp(),
            $this->metaGenerator->getBeginTimestamp()
        );
    }

    /**
     * @param Meta|null $meta
     * @return Meta
     */
    protected function getMeta(?Meta $meta = null): Meta
    {
        if (is_null($meta)) {
            return $this->metaGenerator->generate();
        }

        return $meta;
    }
}