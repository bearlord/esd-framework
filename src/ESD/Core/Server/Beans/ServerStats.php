<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Beans;

/**
 * Class ServerStats
 * @package ESD\Core\Server\Beans
 */
class ServerStats
{
    /**
     * Server start time
     * @var int
     */
    private $startTime;

    /**
     * Number of current connections
     * @var int
     */
    private $connectionNum;

    /**
     * Accepted connections Count
     * @var int
     */
    private $acceptCount;

    /**
     * closed connections count
     * @var int
     */
    private $closeCount;

    /**
     * Currently queued tasking number
     * @var int
     */
    private $taskingNum;

    /**
     * Number of requests received by the server
     * @var int
     */
    private $requestCount;

    /**
     * Number of requests received by the current Worker process
     * @var int
     */
    private $workerRequestCount;

    /**
     * Count of tasks delivered by the master process to the current worker process
     * @var int
     */
    private $workerDispatchCount;

    /**
     * Number of tasks in task queue
     * @var int
     */
    private $taskQueueNum;

    /**
     * Number of bytes of task queue
     * @var int
     */
    private $taskQueueBytes;

    /**
     * Number of current coroutines
     * @var int
     */
    private $coroutineNum;

    public function __construct($data)
    {
        $this->startTime = $data['start_time']??null;
        $this->connectionNum = $data['connection_num']??null;
        $this->acceptCount = $data['accept_count']??null;
        $this->closeCount = $data['close_count']??null;
        $this->taskingNum = $data['tasking_num']??null;
        $this->requestCount = $data['request_count']??null;
        $this->workerRequestCount = $data['worker_request_count']??null;
        $this->workerDispatchCount = $data['worker_dispatch_count']??null;
        $this->taskQueueNum = $data['task_queue_num']??null;
        $this->taskQueueBytes = $data['task_queue_bytes']??null;
        $this->coroutineNum = $data['coroutine_num']??null;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return int
     */
    public function getConnectionNum()
    {
        return $this->connectionNum;
    }

    /**
     * @return int
     */
    public function getAcceptCount()
    {
        return $this->acceptCount;
    }

    /**
     * @return int
     */
    public function getCloseCount()
    {
        return $this->closeCount;
    }

    /**
     * @return int
     */
    public function getTaskingNum()
    {
        return $this->taskingNum;
    }

    /**
     * @return int
     */
    public function getRequestCount()
    {
        return $this->requestCount;
    }

    /**
     * @return int
     */
    public function getWorkerRequestCount()
    {
        return $this->workerRequestCount;
    }

    /**
     * @return int
     */
    public function getWorkerDispatchCount()
    {
        return $this->workerDispatchCount;
    }

    /**
     * @return int
     */
    public function getTaskQueueNum()
    {
        return $this->taskQueueNum;
    }

    /**
     * @return int
     */
    public function getTaskQueueBytes()
    {
        return $this->taskQueueBytes;
    }

    /**
     * @return int
     */
    public function getCoroutineNum()
    {
        return $this->coroutineNum;
    }
}