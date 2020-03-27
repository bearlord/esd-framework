<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 14:39
 */

namespace ESD\Plugins\Scheduled;

use ESD\Core\Plugins\Config\BaseConfig;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledAddEvent;
use ESD\Plugins\Scheduled\Event\ScheduledRemoveEvent;
use ESD\Server\Co\Server;

class ScheduledConfig extends BaseConfig
{
    const key = "scheduled";

    /**
     * 最小间隔时间
     * @var int
     */
    protected $minIntervalTime;

    /**
     * 任务进程数量
     * @var int
     */
    protected $taskProcessCount = 1;

    /**
     * @var string
     */
    protected $taskProcessGroupName = ScheduledTask::GroupName;

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
            throw new ConfigException("定时调度任务的最小时间单位为1s");
        }
    }

    /**
     * 添加调度
     * @param ScheduledTask $scheduledTask
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function addScheduled(ScheduledTask $scheduledTask)
    {
        if (!Server::$isStart || Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() == ScheduledPlugin::processName) {
            //调度进程可以直接添加
            $this->scheduledTasks[$scheduledTask->getName()] = $scheduledTask;
        } else {
            //非调度进程需要动态添加，借助于Event
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ScheduledAddEvent($scheduledTask),
                Server::$instance->getProcessManager()->getProcessFromName(ScheduledPlugin::processName)
            );
        }
    }

    /**
     * 移除调度
     * @param String $scheduledTaskName
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function removeScheduled(String $scheduledTaskName)
    {
        if (!Server::$isStart || Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() == ScheduledPlugin::processName) {
            //调度进程可以直接移除
            unset($this->scheduledTasks[$scheduledTaskName]);
        } else {
            //非调度进程需要动态移除，借助于Event
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ScheduledRemoveEvent($scheduledTaskName),
                Server::$instance->getProcessManager()->getProcessFromName(ScheduledPlugin::processName)
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