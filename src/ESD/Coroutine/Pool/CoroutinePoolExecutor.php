<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Coroutine\Pool;

use ESD\Coroutine\Channel\ChannelImpl;
use ESD\Coroutine\Coroutine;

/**
 * Class CoroutinePoolExecutor
 * @package ESD\Coroutine\Pool
 */
class CoroutinePoolExecutor implements Executor
{
    /**
     * Channel implement
     * @var ChannelImpl
     */
    private $channel;

    /**
     * Is destroyed
     * @var bool
     */
    private $isDestroy = false;

    /**
     * cid集合
     * @var array
     */
    private $cids;

    /**
     * The number of core coroutines in the coroutine pool
     * @var int
     */
    private $corePoolSize;

    /**
     * The maximum number of coroutines allowed in the coroutine pool
     * @var int
     */
    private $maximumPoolSize;

    /**
     * Keep alive time of coroutines when idle, unit milliseconds,
     * @var int
     */
    private $keepAliveTime;

    /**
     * Name
     * @var string
     */
    private $name;

    /**
     * CoroutinePoolExecutor constructor.
     *
     * @param int $corePoolSize The number of core coroutines in the coroutine pool. When a task is submitted,
     * the coroutine pool creates a new coroutine to execute the task until the current number of coroutines equals corePoolSize;
     * if the current number of coroutines is corePoolSize, the tasks that continue to be submitted are saved to the blocking queue,
     * Waiting to be executed; if the prestartAllCoreThreads() method of the coroutine pool is executed,
     * the coroutine pool will create and start all core coroutines in advance.
     * @param int $maximumPoolSize The maximum number of coroutines allowed in the coroutine pool.
     * If the current blocking queue is full and continue to submit tasks, create a new coroutine to execute the task,
     * provided that the current number of coroutines is less than maximumPoolSize;
     * @param float $keepAliveTime Unit seconds, the survival time of the coroutine when it is idle, that is,
     * the survival time when the coroutine has no tasks to execute; by default, this parameter is only useful
     * when the number of coroutines is greater than corePoolSize
     */
    public function __construct(int $corePoolSize, int $maximumPoolSize, float $keepAliveTime)
    {
        $this->channel = new ChannelImpl($corePoolSize);
        $this->cids = [];
        $this->corePoolSize = $corePoolSize;
        $this->maximumPoolSize = $maximumPoolSize;
        $this->keepAliveTime = $keepAliveTime;
    }

    /**
     * The coroutine pool creates and starts all core coroutines in advance.
     */
    public function preStartAllCoreThreads(): void
    {
        for ($i = 0; $i < $this->corePoolSize; $i++) {
            $this->createNewCoroutine(null, 0);
        }
    }

    /**
     * Create new coroutine
     *
     * @param Runnable $runnable
     * @param float $keepAliveTime
     */
    private function createNewCoroutine($runnable, float $keepAliveTime): void
    {
        $cid = goWithContext(function () use ($runnable, $keepAliveTime) {
            \Swoole\Coroutine::defer(function () {
                unset($this->cids[Coroutine::getCid()]);
            });
            if ($runnable != null) {
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                }
                if (is_callable($runnable)) {
                    $runnable();
                }
            }
            while (true) {
                $runnable = $this->channel->pop($keepAliveTime);
                if ($runnable === false) break;
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                } else if (is_callable($runnable)) {
                    $runnable();
                }
            }
        });
        $this->cids[$cid] = $cid;
    }

    /**
     * Destroy
     */
    public function destroy()
    {
        $this->isDestroy = true;
        $this->channel->close();
        $this->cids = null;
        $this->name = null;
        $this->channel = null;
    }

    /**
     * @inheritDoc
     * @param $runnable
     * @throws \Exception
     */
    public function execute($runnable)
    {
        if ($this->isDestroy()) {
            throw new \Exception("Coroutine pool has been destroyed and cannot execute tasks");
        }
        $coroutineCount = count($this->cids);

        //If it is less than the number of core coroutines, tasks will continue to be created and executed
        if ($coroutineCount < $this->corePoolSize) {
            $this->createNewCoroutine($runnable, 0);
            return;
        }
        //When the number of consumers in the channel is 0, it means that the consumers are performing tasks.
        //This is if there are new tasks, and the number of coroutines is less than maximumPoolSize,
        //the coroutines will continue to be created
        if ($this->channel->getStats()->getConsumerNum() == 0 && $coroutineCount < $this->maximumPoolSize) {
            $this->createNewCoroutine($runnable, $this->keepAliveTime);
            return;
        }

        $this->channel->push($runnable);
    }

    /**
     * @return bool
     */
    public function isDestroy(): bool
    {
        return $this->isDestroy;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getCids(): array
    {
        return $this->cids;
    }

}