<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Snowflake\MetaGenerator;

use ESD\Snowflake\MetaGenerator;

class RandomMilliSecondMetaGenerator extends MetaGenerator
{
    /**
     * @param int $beginTimestamp
     */
    public function __construct(int $beginTimestamp = 0)
    {
        parent::__construct($beginTimestamp * 1000);
    }

    /**
     * @return int
     */
    public function getDataCenterId(): int
    {
        return rand(0, 31);
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return rand(0, 31);
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return intval(microtime(true) * 1000);
    }

    /**
     * @return int
     */
    public function getNextTimestamp(): int
    {
        $timestamp = $this->getTimestamp();
        while ($timestamp <= $this->lastTimestamp) {
            $timestamp = $this->getTimestamp();
        }

        return $timestamp;
    }
}
