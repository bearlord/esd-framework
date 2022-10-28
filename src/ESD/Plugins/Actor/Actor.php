<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Actor;

use DI\Annotation\Inject;
use ESD\Core\Channel\Channel;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;
use Swoole\Timer;

/**
 * Class Actor
 * @package ESD\Plugins\Actor
 */
abstract class Actor
{
    use GetLogger;

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
     * @var array data
     */
    protected $data;

    /**
     * @var array timer ids
     */
    protected $timerIds = [];
    
    /**
     * Actor constructor.
     * @param string $name
     * @throws \DI\DependencyException
     * @throws ActorException
     */
    final public function __construct(string $name = '', bool $isCreated = false)
    {
        $this->name = $name;
        Server::$instance->getContainer()->injectOn($this);
        if ($isCreated) {
            ActorManager::getInstance()->addActor($this);
        }

        $this->channel = DIGet(Channel::class, [$this->actorConfig->getActorMailboxCapacity()]);

        //Loop process the information in the mailbox
        goWithContext(function () use ($name) {
            while (true) {
                $message = $this->channel->pop();
                $this->handleMessage($message);
            }
        });
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Init data
     * @param $data
     * @return mixed
     */
    public function initData($data)
    {
        $this->data = $data;
    }

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
     * Destroy
     * @throws \Exception
     */
    public function destroy()
    {
        $this->clearAllTimer();
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
        if ($waitCreate && ActorManager::getInstance()->hasActor($actorName)) {
            return new ActorRPCProxy($actorName, false, $timeOut);
        }

        $processes = Server::$instance->getProcessManager()->getProcessGroup(ActorConfig::GROUP_NAME);

        $nowProcess = ActorManager::getInstance()->getAtomic()->add();
        $index = $nowProcess % count($processes->getProcesses());

        Server::$instance->getEventDispatcher()->dispatchProcessEvent(new ActorCreateEvent(
            ActorCreateEvent::ActorCreateEvent,
            [
                $actionClass, $actorName, $data, true
            ]), $processes->getProcesses()[$index]);

        if ($waitCreate) {
            $call = Server::$instance->getEventDispatcher()->listen(ActorCreateEvent::ActorCreateReadyEvent . ":" . $actorName, null, true);
            $result = $call->wait($timeOut);
            if ($result == null) {
                /*
                throw new ActorException(Yii::t('esd', 'Actor {actor} created timeout', [
                    'actor' => $actorName
                ]));
                */
                return false;
            }

            return new ActorRPCProxy($actorName, false, $timeOut);
        }
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

    /**
     * Tick timer
     * @param int $msec
     * @param callable $callback
     * @param ...$params
     * @return false|int
     */
    public function tick(int $msec, callable $callback, ... $params)
    {
        $id = Timer::tick($msec, $callback, ...$params);
        $this->timerIds[$id] = $id;

        return $id;
    }

    /**
     * After timer
     * @param int $msec
     * @param callable $callback
     * @param ...$params
     * @return false|int
     */
    public function after(int $msec, callable $callback, ... $params)
    {
        $id = Timer::after($msec, $callback, ...$params);
        $this->timerIds[$id] = $id;

        return $id;
    }

    /**
     * Clear timer
     * @param int $id
     * @return void
     */
    public function clearTimer(int $id)
    {
        Timer::clear($id);
        unset($this->timerIds[$id]);
    }

    /**
     * Clear all timer
     * @return void
     */
    public function clearAllTimer()
    {
        if (!empty($this->timerIds)) {
            foreach ($this->timerIds as $timerId) {
                $this->clearTimer($timerId);
            }
            $this->debug(Yii::t("esd", "Actor {actor}'s all timer cleared", [
                "actor" => $this->getName()
            ]));
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function saveContext()
    {
        $class = get_class($this);
        $name = $this->getName();
        $data = $this->getData();

        //Dispatch ActorSaveEvent to actor-cache process, do not need reply
        Server::$instance->getEventDispatcher()->dispatchProcessEvent(new ActorSaveEvent(
            ActorSaveEvent::ActorSaveEvent,
            [
                $class, $name, $data,
            ]), Server::$instance->getProcessManager()->getProcessFromName(ActorCacheProcess::PROCESS_NAME));
    }
}