<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Core\Message\Message;
use ESD\Core\Server\Process\Process;
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
     * @return mixed|void
     */
    public function init()
    {

    }

    /**
     * @inheritDoc
     * @return mixed|void
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

            //Dispatch ActorSaveEvent to actor-cache process, do not need reply
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(new ActorSaveEvent(
                ActorSaveEvent::ActorSaveEvent,
                [
                    $class, $name, $data,
                ]), Server::$instance->getProcessManager()->getProcessFromName('actor-cache'));
        });
    }

    /**
     * @inheritDoc
     * @return mixed|void
     */
    public function onProcessStop()
    {

    }

    /**
     * @inheritDoc
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed|void
     */
    public function onPipeMessage(Message $message, Process $fromProcess)
    {

    }
}