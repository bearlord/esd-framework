<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/28
 * Time: 15:05
 */

namespace ESD\Plugins\Scheduled;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledExecuteEvent;
use ESD\Server\Co\Server;

class ScheduledTaskHandle
{
    use GetLogger;

    public function __construct()
    {
        //监听任务事件的执行
        goWithContext(function () {
            $call = Server::$instance->getEventDispatcher()->listen(ScheduledExecuteEvent::ScheduledExecuteEvent);
            $call->call(function (ScheduledExecuteEvent $event){
                goWithContext(function () use ($event) {
                    $this->execute($event->getTask());
                });
            });
        });
    }

    /**
     * 执行调度
     * @param ScheduledTask $scheduledTask
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function execute(ScheduledTask $scheduledTask)
    {
        $className = $scheduledTask->getClassName();
        $taskInstance = Server::$instance->getContainer()->get($className);
        call_user_func([$taskInstance, $scheduledTask->getFunctionName()]);
        $this->debug("执行{$scheduledTask->getName()}任务");
    }
}