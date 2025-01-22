<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 * copy from hyperf[https://www.hyperf.io/]
 */

namespace ESD\Core\Pool;

interface FrequencyInterface
{

    public function __construct(?Pool $pool = null);

    /**
     * Number of hit per time.
     * @return bool
     */
    public function hit(int $number = 1): bool;

    /**
     * Hits per second.
     * @return float
     */
    public function frequency(): float;

    /**
     * @return bool
     */
    public function isLowFrequency(): bool;

}
