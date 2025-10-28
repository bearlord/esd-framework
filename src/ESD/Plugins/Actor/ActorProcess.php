<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
use ESD\Plugins\Actor\Event\ActorCreateEvent;
use ESD\Plugins\Actor\Event\ActorSaveEvent;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class ActorProcess
 * @package ESD\Plugins\Actor
 */
class ActorProcess extends Process
{
    /**
     * @inheritDoc
     * @return void
     */
    public function init()
    {

    }

    /**
     * @inheritDoc
     * @return void
     * @throws \Exception
     */
    public function onProcessStart()
    {
        $call = $this->eventDispatcher->listen(ActorCreateEvent::ActorCreateEvent);
        $call->call(function (ActorCreateEvent $event) {
            $class = $event->getData()[0];
            $name = $event->getData()[1];
            $data = $event->getData()[2] ?? null;
            $isCreated = $event->getData()[3] ?? false;
            $actor = new $class($name, $isCreated);
            if ($actor instanceof Actor) {
                $actor->initData($data);
            } else {
                throw new ActorException(Yii::t('esd', '{class} is not a actor', [
                    'class' => $class
                ]));
            }
            $this->eventDispatcher->dispatchProcessEvent(new ActorCreateEvent(ActorCreateEvent::ActorCreateReadyEvent . ":" . $actor->getName(), null),
                Server::$instance->getProcessManager()->getProcessFromId($event->getProcessId())
            );
        });
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function onProcessStop()
    {

    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {

    }
}
