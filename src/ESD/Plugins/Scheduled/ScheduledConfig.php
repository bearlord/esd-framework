<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledAddEvent;
use ESD\Plugins\Scheduled\Event\ScheduledRemoveEvent;
use ESD\Server\Co\Server;
use ESD\Yii\Yii;

/**
 * Class ScheduledConfig
 * @package ESD\Plugins\Scheduled
 */
class ScheduledConfig extends BaseConfig
{
    const key = "scheduled";

    /**
     * Minimum interval
     * @var int
     */
    protected $minIntervalTime;

    /**
     * Task processes count
     * @var int
     */
    protected $taskProcessCount = 1;

    /**
     * @var string
     */
    protected $taskProcessGroupName = ScheduledTask::GROUP_NAME;

    /**
     * @var ScheduledTask[]
     */
    protected $scheduledTasks = [];


    /**
     * ScheduledConfig constructor.
     * @param int $minIntervalTime
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function __construct($minIntervalTime = 1000)
    {
        parent::__construct(self::key);
        $this->minIntervalTime = $minIntervalTime;
        if ($minIntervalTime < 1000) {
            throw new ConfigException(Yii::t('esd', 'The minimum time unit for scheduled tasks is 1s'));
        }
    }

    /**
     * Add Scheduled
     * @param ScheduledTask $scheduledTask
     * @throws \Exception
     */
    public function addScheduled(ScheduledTask $scheduledTask)
    {
        if (!Server::$isStart || Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() == ScheduledPlugin::PROCESS_NAME) {
            //The scheduled process can directly add tasks
            $this->scheduledTasks[$scheduledTask->getName()] = $scheduledTask;
        } else {
            //Non-scheduled processes need to be added dynamically, with the help of Event
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ScheduledAddEvent($scheduledTask),
                Server::$instance->getProcessManager()->getProcessFromName(ScheduledPlugin::PROCESS_NAME)
            );
        }
    }

    /**
     * Remove scheduled
     * @param String $scheduledTaskName
     * @throws \Exception
     */
    public function removeScheduled(String $scheduledTaskName)
    {
        if (!Server::$isStart || Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() == ScheduledPlugin::PROCESS_NAME) {
            //The scheduling process can be removed directly
            unset($this->scheduledTasks[$scheduledTaskName]);
        } else {
            //Non-scheduled processes need to be removed dynamically, with the help of Event
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ScheduledRemoveEvent($scheduledTaskName),
                Server::$instance->getProcessManager()->getProcessFromName(ScheduledPlugin::PROCESS_NAME)
            );
        }
    }

    /**
     * @return int
     */
    public function getMinIntervalTime(): int
    {
        return $this->minIntervalTime;
    }

    /**
     * @return ScheduledTask[]
     */
    public function getScheduledTasks(): array
    {
        return $this->scheduledTasks;
    }

    /**
     * @param array $scheduledTasks
     * @throws \ReflectionException
     */
    public function setScheduledTasks(array $scheduledTasks): void
    {
        foreach ($scheduledTasks as $key => $scheduledTask) {
            if ($scheduledTask instanceof ScheduledTask) {
                $this->scheduledTasks[$scheduledTask->getName()] = $scheduledTask;
            } else {
                $scheduledTaskInstance = new ScheduledTask(null, null, null, null);
                $scheduledTaskInstance->buildFromConfig($scheduledTask);
                $scheduledTaskInstance->setName($key);
                $this->scheduledTasks[$scheduledTaskInstance->getName()] = $scheduledTaskInstance;
            }
        }
    }

    /**
     * @param int $minIntervalTime
     */
    public function setMinIntervalTime(int $minIntervalTime): void
    {
        $this->minIntervalTime = $minIntervalTime;
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