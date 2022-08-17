<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use ESD\Server\Coroutine\Server;
use ESD\Plugins\ProcessRPC\ProcessRPCCallMessage;
use ESD\Plugins\ProcessRPC\RPCProxy;
use ESD\Yii\Yii;

/**
 * Class ActorRPCProxy
 * @package ESD\Plugins\Actor
 */
class ActorRPCProxy extends RPCProxy
{
    /**
     * ActorRPCProxy constructor.
     * @param string $actorName
     * @param bool $oneway
     * @param float $timeOut
     * @throws ActorException
     */
    public function __construct(string $actorName, bool $oneway, float $timeOut = 0)
    {
        $actorInfo = ActorManager::getInstance()->getActorInfo($actorName);
        if ($actorInfo == null) {
            throw new ActorException(Yii::t('esd', 'Actor {actor} not exist', [
                '{actor}' => $actorName
            ]));
        }
        parent::__construct($actorInfo->getProcess(), $actorInfo->getClassName() . ":" . $actorInfo->getName(), $oneway, $timeOut);
    }

    /**
     * Send message
     * @param $message
     */
    public function sendMessage(ActorMessage $message)
    {
        $message = new ProcessRPCCallMessage($this->className, "sendMessage", [$message], true);
        Server::$instance->getProcessManager()->getCurrentProcess()->sendMessage($message, $this->process);
    }
}