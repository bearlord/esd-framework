<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Pool;

use ESD\Coroutine\Channel\ChannelImpl;
use ESD\Coroutine\Co;

class Runnable
{
    /**
     * @var ChannelImpl
     */
    private $channel;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var callable
     */
    private $runnable;

    /**
     * Runnable constructor.
     * @param callable $runnable
     * @param bool $needResult
     */
    public function __construct(callable $runnable, bool $needResult = false)
    {
        $this->runnable = $runnable;
        if ($needResult) {
            $this->channel = new ChannelImpl();
        }
    }

    /**
     * Get result
     *
     * @param float $timeOut
     * @return mixed
     */
    public function getResult(float $timeOut = 0)
    {
        if ($this->channel == null) {
            return null;
        }
        if ($this->result == null) {
            $this->result = $this->channel->pop($timeOut);
        }
        $this->channel->close();
        return $this->result;
    }

    /**
     * 发送结果
     * @param $result
     */
    public function sendResult($result)
    {
        if ($this->channel == null) {
            return;
        }
        $this->channel->push($result);
    }

    /**
     * Just run
     */
    public function justRun()
    {
        Co::runTask($this);
    }

    /**
     * @return mixed
     */
    public function run()
    {
        return call_user_func($this->runnable);
    }
}