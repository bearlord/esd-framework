<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Memory\CrossProcess;

/**
 * Class Atomic
 * Atomic counting operation class, which can facilitate the lock-free atomic increase and decrease of integers.
 * Use shared memory to operate counting between different processes
 *
 * @package ESD\Core\Memory\CrossProcess
 */
class AtomicLong
{
    private $swooleAtomicLong;

    /**
     * AtomicLong constructor.
     * Can manipulate 64-bit signed integers
     *
     * @param int $initValue
     */
    public function __construct(int $initValue = 0)
    {
        $this->swooleAtomicLong = new \Swoole\Atomic\Long($initValue);
    }

    /**
     * Add value
     *
     * @param int $addValue
     * @return int
     */
    public function add(int $addValue = 1): int
    {
        return $this->swooleAtomicLong->add($addValue);
    }

    /**
     * Sub value
     *
     * @param int $subValue
     * @return int
     */
    public function sub(int $subValue = 1): int
    {
        return $this->swooleAtomicLong->sub($subValue);
    }

    /**
     * Get
     *
     * @return int
     */
    public function get()
    {
        return $this->swooleAtomicLong->get();
    }

    /**
     * Set value
     *
     * @param int $value
     */
    public function set(int $value): void
    {
        $this->swooleAtomicLong->set($value);
    }

    /**
     * If the current value is equal to parameter 1, set the current value to parameter 2
     *
     * @param int $cmp_value
     * @param int $set_value
     * @return bool  如果不等于返回false
     */
    public function cmpset(int $cmp_value, int $set_value): bool
    {
        return $this->swooleAtomicLong->cmpset($cmp_value, $set_value);
    }
}
