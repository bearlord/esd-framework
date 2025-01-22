<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 * copy from hyperf[https://www.hyperf.io/]
 */

namespace ESD\Core\Pool;

class DefaultFrequency implements FrequencyInterface
{
    /**
     * @var \ESD\Core\Pool\Pool|null
     */
    protected $pool;

    protected array $hits = [];

    /**
     * How much time do you want to calculate the frequency ?
     */
    protected $time = 10;

    protected $lowFrequency = 5;

    protected $beginTime;

    protected $lowFrequencyTime;

    protected $lowFrequencyInterval = 60;

    public function __construct(?Pool $pool = null)
    {
        $this->pool = $pool;
        $this->beginTime = time();
        $this->lowFrequencyTime = time();
    }

    /**
     * @param int $number
     * @return bool
     */
    public function hit(int $number = 1): bool
    {
        $this->flush();

        $now = time();
        $hit = $this->hits[$now] ?? 0;
        $this->hits[$now] = $number + $hit;

        return true;
    }

    /**
     * @return float
     */
    public function frequency(): float
    {
        $this->flush();

        $hits = 0;
        $count = 0;
        foreach ($this->hits as $hit) {
            ++$count;
            $hits += $hit;
        }

        return floatval($hits / $count);
    }

    /**
     * @return bool
     */
    public function isLowFrequency(): bool
    {
        $now = time();
        if ($this->lowFrequencyTime + $this->lowFrequencyInterval < $now && $this->frequency() < $this->lowFrequency) {
            $this->lowFrequencyTime = $now;
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    protected function flush(): void
    {
        $now = time();
        $latest = $now - $this->time;
        foreach ($this->hits as $time => $hit) {
            if ($time < $latest) {
                unset($this->hits[$time]);
            }
        }

        if (count($this->hits) < $this->time) {
            $beginTime = max($this->beginTime, $latest);
            for ($i = $beginTime; $i < $now; ++$i) {
                $this->hits[$i] = $this->hits[$i] ?? 0;
            }
        }
    }

}
