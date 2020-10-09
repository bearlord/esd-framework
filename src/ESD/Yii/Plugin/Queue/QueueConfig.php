<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Yii\Plugins\Queue;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledAddEvent;
use ESD\Plugins\Scheduled\Event\ScheduledRemoveEvent;
use ESD\Server\Co\Server;
use ESD\Yii\Plugin\Queue\Beans\QueueTask;
use ESD\Yii\Yii;

/**
 * Class QueueConfig
 * @package ESD\Plugins\Scheduled
 */
class QueueConfig extends BaseConfig
{
    const KEY = "queue";

    /**
     * Task processes count
     * @var int
     */
    protected $taskProcessCount = 1;

    /**
     * @var string
     */
    protected $taskProcessGroupName = QueueTask::GROUP_NAME;

    /**
     * @var ScheduledTask[]
     */
    protected $queueTasks = [];

    /**
     * QueueConfig constructor.
     */
    public function __construct()
    {
        parent::__construct(self::KEY);
    }

    /**
     * @return ScheduledTask[]
     */
    public function getQueueTasks(): array
    {
        return $this->queueTasks;
    }

    /**
     * @param array $queueTasks
     * @throws \ReflectionException
     */
    public function setQueueTasks(array $queueTasks): void
    {
        foreach ($queueTasks as $key => $queueTask) {
            if ($queueTask instanceof ScheduledTask) {
                $this->queueTasks[$queueTask->getName()] = $queueTask;
            } else {
                $queueTaskInstance = new QueueTask(null);
                $queueTaskInstance->buildFromConfig($queueTask);
                $queueTaskInstance->setName($key);
                $this->queueTasks[$queueTaskInstance->getName()] = $queueTaskInstance;
            }
        }
    }

    /**
     * @return int
     */
    public function getTaskProcessCount(): int
    {
        return $this->taskProcessCount;
    }

    /**
     * @param int $taskProcessCount
     */
    public function setTaskProcessCount(int $taskProcessCount): void
    {
        $this->taskProcessCount = $taskProcessCount;
    }

    /**
     * @return string
     */
    public function getTaskProcessGroupName(): string
    {
        return $this->taskProcessGroupName;
    }

    /**
     * @param string $taskProcessGroupName
     */
    public function setTaskProcessGroupName(string $taskProcessGroupName): void
    {
        $this->taskProcessGroupName = $taskProcessGroupName;
    }
}