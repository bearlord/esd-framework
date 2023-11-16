<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Server\Coroutine\Server;

/**
 * Trait GetActorRpc
 * @package ESD\Plugins\Actor
 */
trait GetActorRpc
{
    /**
     * Call actor
     * @param string $actorName
     * @param bool $oneway
     * @param float $timeOut
     * @return ActorRPCProxy
     * @throws ActorException
     */
    public function callActor(string $actorName, bool $oneway = false, float $timeOut = 5): ActorRPCProxy
    {
        return new ActorRPCProxy($actorName, $oneway, $timeOut);
    }

    /**
     * Only the process that created the Actor can use this listener
     * @param string $actorName
     * @param float $timeOut
     * @throws \ESD\Plugins\Actor\ActorException
     */
    public function waitActorCreate(string $actorName, float $timeOut = 5)
    {
        if (!ActorManager::getInstance()->hasActor($actorName)) {
            $call = Server::$instance->getEventDispatcher()->listen(ActorCreateEvent::ActorCreateReadyEvent . ":" . $actorName, null, true);
            $result = $call->wait($timeOut);
            if ($result == null) {
                throw new ActorException("wait actor create timeout");
            }
        }
    }
}
