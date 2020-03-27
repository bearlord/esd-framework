<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Channel;

use ESD\Core\Channel\Channel;
use ESD\Coroutine\Beans\ChannelStats;

/**
 * Class ChannelImpl
 * Channel, similar to chan of go language, supports multi-producer coroutines and multi-consumer coroutines.
 * The bottom layer automatically implements the switching and scheduling of coroutines.
 * The channel is similar to PHP's Array, it only takes up memory, no other additional resource requests,
 * all operations are memory operations, no IO consumption.
 *
 * @package ESD\Coroutine
 */
class ChannelImpl implements Channel
{
    const CHANNEL_OK = SWOOLE_CHANNEL_OK;
    const CHANNEL_TIMEOUT = SWOOLE_CHANNEL_TIMEOUT;
    const CHANNEL_CLOSED = SWOOLE_CHANNEL_CLOSED;

    public $swooleChannel;

    /**
     * ChannelImpl constructor.
     * @param int $capacity
     */
    public function __construct(int $capacity = 1)
    {
        $this->swooleChannel = new \Swoole\Coroutine\Channel($capacity);
    }

    /**
     * Write data to the channel.
     * To avoid ambiguity, do not write empty data to the channel, such as 0, false, empty string, null
     *
     * @param mixed $data Can be any type of PHP variable, including anonymous functions and resources
     * @param float $timeout Set the timeout time. When the channel is full, push will suspend the current coroutine.
     * In the agreed time, if there is no consumer consumption data, a timeout will occur, the underlying layer will
     * resume the current coroutine, and the push call immediately returns false Write failed
     * @return bool
     */
    public function push($data, float $timeout = -1): bool
    {
        return $this->swooleChannel->push($data, $timeout);
    }

    /**
     * Read data from the channel.
     * The return value can be any type of PHP variable, including anonymous functions and resources
     * When the channel is closed, the execution fails and return false
     *
     * @param float $timeout specifies the timeout time, floating point type, unit is second, the minimum granularity
     * is millisecond, if there is no producer push data within the specified time, it will returns false
     * @return mixed
     */
    public function pop(float $timeout = 0)
    {
        return $this->swooleChannel->pop($timeout);
    }

    /**
     * Loop pop
     * @param $callback
     */
    public function popLoop(callable $callback)
    {
        goWithContext(function () use ($callback) {
            while (true) {
                $result = $this->pop();
                if ($result === false) break;
                $callback($result);
            }
        });
    }

    /**
     * Channel stats
     *
     * @return ChannelStats
     */
    public function getStats(): ChannelStats
    {
        return new ChannelStats($this->swooleChannel->stats());
    }

    /**
     * Close the channel. And wake up all coroutines waiting to read and write.
     */
    public function close()
    {
        $this->swooleChannel->close();
    }

    /**
     * Get the number of elements in the channel
     *
     * @return int
     */
    public function length(): int
    {
        return $this->swooleChannel->length();
    }

    /**
     * Determine if the current channel is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->swooleChannel->isEmpty();
    }

    /**
     * Determine if the current channel is full
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->swooleChannel->isFull();
    }

    /**
     * The capacity set in the constructor will be saved here, but if the set capacity is less than 1,
     * this variable will be equal to 1
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->swooleChannel->capacity;
    }

    /**
     * The capacity set in the constructor will be saved here, but if the set capacity is less than 1,
     * this variable will be equal to 1
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->swooleChannel->errCode;
    }
}