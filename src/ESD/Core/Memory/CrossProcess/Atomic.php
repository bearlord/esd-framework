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
class Atomic
{
    /**
     * @var \Swoole\Atomic
     */
    private $swooleAtomic;

    /**
     * Atomic constructor.
     * Only 32-bit unsigned integers can be operated, the maximum support is 4.2 billion, negative numbers are not supported
     *
     * @param int $initValue
     */
    public function __construct(int $initValue = 0)
    {
        $this->swooleAtomic = new \Swoole\Atomic($initValue);
    }

    /**
     * Add value
     * @param int $addValue
     * @return int
     */
    public function add(int $addValue = 1): int
    {
        return $this->swooleAtomic->add($addValue);
    }

    /**
     * Sub value
     *
     * @param int $subValue
     * @return int
     */
    public function sub(int $subValue = 1): int
    {
        return $this->swooleAtomic->sub($subValue);
    }

    /**
     * Get value
     *
     * @return int
     */
    public function get(): int
    {
        return $this->swooleAtomic->get();
    }

    /**
     * Set value
     *
     * @param int $value
     */
    public function set(int $value): void
    {
        $this->swooleAtomic->set($value);
    }

    /**
     * If the current value is equal to parameter 1, set the current value to parameter 2
     *
     * @param int $cmp_value
     * @param int $set_value
     * @return mixed
     */
    public function cmpset(int $cmp_value, int $set_value)
    {
        return $this->swooleAtomic->cmpset($cmp_value, $set_value);
    }

    /**
     * When the atomic count value is 0, the program enters the waiting state.
     * When using the wait / wakeup feature, the value of the atomic count can only be 0 or 1, otherwise it will not work properly
     * Another process calling wakeup can wake up the program again.
     * The bottom layer is implemented based on Linux Futex.
     * Using this feature, you can implement a wait, notification, and lock function using only 4 bytes of memory.
     *
     * @param float $timeout Specifies the timeout period. The default is 1 second. When set to -1,
     * it will never time out, and will continue to wait until other processes wake up
     * @return bool Timeout returns false, and the error code is EAGAIN.
     * Success returns true, indicating that another process successfully awakened the current lock through wakeup.
     * Of course, when the value of the atomic count is 1, it means that there is no need to enter the waiting state,
     * and the resource is currently available. wait function will return true immediately
     */
    public function wait(float $timeout = 1.0): bool
    {
        return $this->swooleAtomic->wait($timeout);
    }

    /**
     * Wake up other processes in the wait state.
     * If the current atomic count is 0, it means that no process is waiting, wakeup will return true immediately
     * If the current atomic count is 1, it means that a process is currently waiting, wakeup will wake up the waiting process and return true
     * If multiple processes are in the wait state at the same time, the $n parameter can control the number of processes awakened
     * After the awakened process returns, the atomic count will be set to 0, then you can call wakeup again to wake up other processes that are waiting
     *
     * @param int $n
     * @return mixed
     */
    public function wakeup(int $n = 1)
    {
        return $this->swooleAtomic->wakeup($n);
    }
}