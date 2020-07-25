<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
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
use ESD\Yii\Yii;

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
     * Process scheduled count
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
     * @inheritDoc
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
        $pluginInterfaceManager->addPlugin(new AnnotationsScanPlugin());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "Scheduled";
    }

    /**
     * Before server start
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        //Add helper process
        $this->scheduledConfig->merge();
        Server::$instance->addProcess(self::processName, HelperScheduledProcess::class, self::processGroupName);
        //Add scheduled process
        for ($i = 0; $i < $this->scheduledConfig->getTaskProcessCount(); $i++) {
            Server::$instance->addProcess("scheduled-$i", ScheduledProcess::class, ScheduledTask::GroupName);
        }
    }

    /**
     * Before process start
     *
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public function beforeProcessStart(Context $context)
    {
        new ScheduledTaskHandle();

        //Help process
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::processName) {
            //Scan annotation
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
                        $this->warn(Yii::t('esd', 'The {name} task is not set to cron and has been ignored', [
                            'name' => $scheduledTask->name
                        ]));
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

            //Initialize the counter
            foreach (Server::$instance->getProcessManager()->getProcesses() as $process) {
                $this->processScheduledCount[$process->getProcessId()] = 0;
            }

            //Listen to dynamically added/removed task events
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

            //Add timer scheduled task
            addTimerTick($this->scheduledConfig->getMinIntervalTime(), function () {
                foreach ($this->scheduledConfig->getScheduledTasks() as $scheduledTask) {
                    if ($scheduledTask->getCron()->isDue()) {
                        //Sort by the number of executions from small to large
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
                            $this->warn('esd', 'The {name} task did not find a scheduled process', [
                                'name' => $scheduledTask->getName()
                            ]);
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