<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use DI\Annotation\Inject;
use ESD\Core\Channel\Channel;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;

/**
 * Class Actor
 * @package ESD\Plugins\Actor
 */
abstract class Actor
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @Inject()
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @Inject()
     * @var ActorConfig
     */
    protected $actorConfig;

    /**
     * @var string
     */
    protected $name;

    /**
     * Actor constructor.
     * @param string $name
     * @throws \DI\DependencyException
     * @throws ActorException
     */
    final public function __construct(string $name = '')
    {
        $this->name = $name;
        Server::$instance->getContainer()->injectOn($this);
        ActorManager::getInstance()->addActor($this);

        $this->channel = DIGet(Channel::class, [$this->actorConfig->getActorMailboxCapacity()]);

        //Loop process the information in the mailbox
        goWithContext(function () {
            while (true) {
                $message = $this->channel->pop();
                $this->handleMessage($message);
            }
        });
    }

    /**
     * Init data
     * @param $data
     * @return mixed
     */
    abstract public function initData($data);

    /**
     * Process the received message
     * @param $message
     * @return mixed
     */
    abstract protected function handleMessage(ActorMessage $message);

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Destory
     */
    public function destroy()
    {
        ActorManager::getInstance()->removeActor($this);
    }

    /**
     * Get proxy
     * @param string $actorName
     * @param bool $oneway
     * @param int $timeOut
     * @return static
     * @throws ActorException
     */
    public static function getProxy(string $actorName, $oneway = false, $timeOut = 5)
    {
        return new ActorRPCProxy($actorName, $oneway, $timeOut);
    }

    /**
     * Create
     * @param string $actorName
     * @param null $data
     * @param bool $waitCreate
     * @param int $timeOut
     * @return static
     * @throws ActorException
     */
    public static function create(string $actionClass, string $actorName, $data = null, $waitCreate = true, $timeOut = 5)
    {
        $processes = Server::$instance->getProcessManager()->getProcessGroup(ActorConfig::GROUP_NAME);

        $nowProcess = ActorManager::getInstance()->getAtomic()->add();
        $index = $nowProcess % count($processes->getProcesses());

        Server::$instance->getEventDispatcher()->dispatchProcessEvent(new ActorCreateEvent(
            ActorCreateEvent::ActorCreateEvent,
            [
                $actionClass, $actorName, $data
            ]), $processes->getProcesses()[$index]);

        if ($waitCreate) {
            if (!ActorManager::getInstance()->hasActor($actorName)) {
                $call = Server::$instance->getEventDispatcher()->listen(ActorCreateEvent::ActorCreateReadyEvent . ":" . $actorName, null, true);
                $result = $call->wait($timeOut);
                if ($result == null) {
                    throw new ActorException(Yii::t('esd', 'Actor {actor} created timeout', [
                        'actor' => $actorName
                    ]));
                }
            }
        }
        return new ActorRPCProxy($actorName, false, $timeOut);
    }

    /**
     * Proxy receive a message, throw it in the mailbox
     * @param ActorMessage $message
     */
    public function sendMessage(ActorMessage $message)
    {
        $this->channel->push($message);
    }

    /**
     * Start transaction
     * @param callable $call
     */
    public function startTransaction(callable $call)
    {

    }
}