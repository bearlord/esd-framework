<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 * copy from hyperf[https://www.hyperf.io/]
 */

namespace ESD\Core\Pool;

use ESD\Coordinator\Timer;


class ConstantFrequency implements FrequencyInterface
{
    /**
     * @var \ESD\Core\Pool\Pool|null
     */
    protected $pool;

    /**
     * @var \ESD\Coordinator\Timer
     */
    protected $timer;

    /**
     * @var int|null
     */
    protected $timerId = null;

    /**
     * @var int
     */
    protected int $interval = 10000;


    public function __construct(?Pool $pool = null)
    {
        $this->pool = $pool;

        $this->timer = new Timer();
        if ($pool) {
            $this->timerId = $this->timer->tick(
                $this->interval / 1000,
                function () {
                    $this->pool->flushOne();
                }
            );
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    /**
     * @return void
     */
    public function clear()
    {
        if ($this->timerId) {
            $this->timer->clear($this->timerId);
        }
        $this->timerId = null;
    }

    /**
     * @return bool
     */
    public function isLowFrequency(): bool
    {
        return false;
    }

    /**
     * @param int $number
     * @return bool
     */
    public function hit(int $number = 1): bool
    {
        return true;
    }

    /**
     * @return float
     */
    public function frequency(): float
    {
        return 0.0;
    }


}
