<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 14:37
 */

namespace ESD\Plugins\Scheduled;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Event\Event;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\AnnotationsScan\ScanClass;
use ESD\Plugins\Scheduled\Annotation\Scheduled;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledAddEvent;
use ESD\Plugins\Scheduled\Event\ScheduledExecuteEvent;
use ESD\Plugins\Scheduled\Event\ScheduledRemoveEvent;
use ESD\Server\Co\Server;

class ScheduledPlugin extends AbstractPlugin
{
    use GetLogger;
    const processName = "helper";
    const processGroupName = "HelperGroup";

    /**
     * @var ScheduledConfig
     */
    private $scheduledConfig;

    /**
     * 进程任务调度次数
     * @var array
     */
    private $processScheduledCount = [];

    /**
     * ScheduledPlugin constructor.
     * @param ScheduledConfig|null $scheduledConfig
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function __construct(ScheduledConfig $scheduledConfig = null)
    {
        parent::__construct();
        if ($scheduledConfig == null) {
            $scheduledConfig = new ScheduledConfig();
        }
        $this->scheduledConfig = $scheduledConfig;
        $this->atAfter(AnnotationsScanPlugin::class);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AnnotationsScanPlugin());
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Scheduled";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        //添加一个helper进程
        $this->scheduledConfig->merge();
        Server::$instance->addProcess(self::processName, HelperScheduledProcess::class, self::processGroupName);
        //添加任务进程
        for ($i = 0; $i < $this->scheduledConfig->getTaskProcessCount(); $i++) {
            Server::$instance->addProcess("scheduled-$i", ScheduledProcess::class, ScheduledTask::GroupName);
        }
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public function beforeProcessStart(Context $context)
    {
        new ScheduledTaskHandle();
        //Help进程
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::processName) {
            //查看注解
            $scanClass = Server::$instance->getContainer()->get(ScanClass::class);
            $reflectionMethods = $scanClass->findMethodsByAnn(Scheduled::class);
            foreach ($reflectionMethods as $reflectionMethod) {
                $reflectionClass = $reflectionMethod->getParentReflectClass();
                $scheduled = $scanClass->getCachedReader()->getMethodAnnotation($reflectionMethod->getReflectionMethod(), Scheduled::class);
                if ($scheduled instanceof Scheduled) {
                    if (empty($scheduled->name)) {
                        $scheduled->name = $reflectionClass->getName() . "::" . $reflectionMethod->getName();
                    }
                    if (empty($scheduled->cron)) {
                        $this->warn("{$scheduled->name}任务没有设置cron，已忽略");
                        continue;
                    }
                    $scheduledTask = new ScheduledTask(
                        $scheduled->name,
                        $scheduled->cron,
                        $reflectionClass->getName(),
                        $reflectionMethod->getName(),
                        $scheduled->processGroup);
                    $this->scheduledConfig->addScheduled($scheduledTask);
                }
            }

            //初始化计数器
            foreach (Server::$instance->getProcessManager()->getProcesses() as $process) {
                $this->processScheduledCount[$process->getProcessId()] = 0;
            }
            //监听动态添加/移除的任务事件
            goWithContext(function () {
                $call = Server::$instance->getEventDispatcher()->listen(ScheduledAddEvent::ScheduledAddEvent);
                Server::$instance->getEventDispatcher()->listen(ScheduledRemoveEvent::ScheduledRemoveEvent, $call);
                $call->call(function (Event $event) {
                    if ($event instanceof ScheduledAddEvent) {
                        $this->scheduledConfig->addScheduled($event->getTask());
                    } else if ($event instanceof ScheduledRemoveEvent) {
                        $this->scheduledConfig->removeScheduled($event->getTaskName());
                    }
                });
            });
            //添加定时器调度
            addTimerTick($this->scheduledConfig->getMinIntervalTime(), function () {
                foreach ($this->scheduledConfig->getScheduledTasks() as $scheduledTask) {
                    if ($scheduledTask->getCron()->isDue()) {
                        //按执行次数从小到大排列
                        asort($this->processScheduledCount);
                        $process = null;
                        foreach ($this->processScheduledCount as $id => $value) {
                            if ($scheduledTask->getProcessGroup() == ScheduledTask::ProcessGroupAll) {
                                $process = Server::$instance->getProcessManager()->getProcessFromId($id);
                                break;
                            } else {
                                if (Server::$instance->getProcessManager()->getProcessFromId($id)->getGroupName() == $scheduledTask->getProcessGroup()) {
                                    $process = Server::$instance->getProcessManager()->getProcessFromId($id);
                                    break;
                                }
                            }
                        }
                        if ($process != null) {
                            $this->processScheduledCount[$process->getProcessId()]++;
                            Server::$instance->getEventDispatcher()->dispatchProcessEvent(new ScheduledExecuteEvent($scheduledTask), $process);
                        } else {
                            $this->warn("{$scheduledTask->getName()}任务没有找到可以调度的进程");
                        }
                    }
                }
            });
        }
        $this->ready();
    }

    /**
     * @return ScheduledConfig
     */
    public function getScheduledConfig(): ScheduledConfig
    {
        return $this->scheduledConfig;
    }

    /**
     * @param ScheduledConfig $scheduledConfig
     */
    public function setScheduledConfig(ScheduledConfig $scheduledConfig): void
    {
        $this->scheduledConfig = $scheduledConfig;
    }
}