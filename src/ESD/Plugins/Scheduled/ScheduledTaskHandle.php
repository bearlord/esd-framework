<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Scheduled;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Scheduled\Beans\ScheduledTask;
use ESD\Plugins\Scheduled\Event\ScheduledExecuteEvent;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class ScheduledTaskHandle
 * @package ESD\Plugins\Scheduled
 */
class ScheduledTaskHandle
{
    use GetLogger;

    public function __construct()
    {
        //Listen the execution of task events
        goWithContext(function () {
            $call = Server::$instance->getEventDispatcher()->listen(ScheduledExecuteEvent::SCHEDULED_EXECUTE_EVENT);
            $call->call(function (ScheduledExecuteEvent $event){
                goWithContext(function () use ($event) {
                    $this->execute($event->getTask());
                });
            });
        });
    }

    /**
     * Execute scheduled task
     *
     * @param ScheduledTask $scheduledTask
     * @throws \Exception
     */
    public function execute(ScheduledTask $scheduledTask)
    {
        $className = $scheduledTask->getClassName();
        $taskInstance = Server::$instance->getContainer()->get($className);
        call_user_func([$taskInstance, $scheduledTask->getFunctionName()]);
        $this->debug(Yii::t('esd', 'Execute scheduled task {name}', [
            'name' => $scheduledTask->getName()
        ]));
    }
}