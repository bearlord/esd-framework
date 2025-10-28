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
use ESD\Plugins\Actor\Event\ActorCreateEvent;
use ESD\Plugins\Actor\Event\ActorSaveEvent;
use ESD\Plugins\Actor\Log\LogFactory;
use ESD\Plugins\Actor\Log\Logger;
use ESD\Plugins\Actor\Multicast\MulticastConfig;
use ESD\Plugins\Actor\Multicast\Channel as MulticastChannel;
use ESD\Plugins\ProcessRPC\GetProcessRpc;
use ESD\Server\Coroutine\Server;
use ESD\Yii\Yii;
use Swoole\Timer;

abstract class Actor
{
    use GetLogger;

    use GetProcessRpc;

    /**
     * @var MulticastConfig
     */
    protected $multicastConfig;

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
     * @var string|null
     */
    protected ?string $name;

    /**
     * @var array|null data
     */
    protected ?array $data = null;

    /**
     * @var array timer ids
     */
    protected array $timerIds = [];

    /**
     * @var Logger
     */
    protected Logger $logHandle;


    /**
     * Actor constructor.
     * @param string|null $name
     * @param bool $isCreated
     * @throws ActorException
     */
    final public function __construct(?string $name = '', bool $isCreated = false)
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
                $this->onHandleMessage($message);
            }
        });

        $this->logHandle = LogFactory::create($name);

        $this->tick(10 * 1000, [$this, 'saveContext']);
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
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
     * @return void
     */
    public function initData($data)
    {
        $this->data = $data;
    }

    /**
     * @param ActorMessage $message
     * @return void
     */
    protected function onHandleMessage(ActorMessage $message)
    {
        $type = $message->getType();

        switch ($type) {
            case ActorMessage::TYPE_MULTICAST:
                $this->handleMulticastMessage($message);
                break;

            case ActorMessage::TYPE_COMMON:
            default:
                $this->handleMessage($message);
        }
    }

    abstract protected function handleMulticastMessage(ActorMessage $message);

    /**
     * Process the received message
     * @param ActorMessage $message
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
     * @param float|null $timeOut
     * @return ActorRPCProxy|false
     */
    public static function getProxy(string $actorName, ?bool $oneway = false, ?float $timeOut = 5)
    {
        try {
            return new ActorRPCProxy($actorName, $oneway, $timeOut);
        } catch (ActorException $exception) {
            return false;
        }
    }

    /**
     * Create
     * @param string $actionClass
     * @param string $actorName
     * @param null $data
     * @param bool $waitCreate
     * @param float|null $timeOut
     * @return ActorRPCProxy|false|void
     * @throws ActorException
     */
    public static function create(string $actionClass, string $actorName, $data = null, ?bool $waitCreate = true, ?float $timeOut = 5)
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
     * @return int
     */
    public function after(int $msec, callable $callback, ... $params): int
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
     * @throws \Exception
     */
    public function clearAllTimer(): bool
    {
        if (!empty($this->timerIds)) {
            foreach ($this->timerIds as $timerId) {
                $this->clearTimer($timerId);
            }

            $this->debug(sprintf("Actor %s's all timer cleared", $this->getName()));
        }
        return true;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function saveContext(): void
    {
        $this->logHandle->log($this->data);
    }


    /**
     * @return MulticastConfig|mixed
     * @throws \Exception
     */
    protected function getMulticastConfig(): MulticastConfig
    {
        if ($this->multicastConfig == null) {
            $this->multicastConfig = DIGet(MulticastConfig::class);
        }

        return $this->multicastConfig;
    }


    /**
     * Subscribe
     *
     * @param string $channel
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException|\ESD\Core\Exception
     */
    public function subscribe(string $channel)
    {
        $actor = $this->getName();

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);

        $rpcProxy->subscribe($channel, $actor);
    }

    /**
     * Unsubscribe
     *
     * @param string $channel
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     * @throws \Exception
     */
    public function unsubscribe(string $channel)
    {
        $actor = $this->getName();

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);
        $rpcProxy->unsubscribe($channel, $actor);
    }

    /**
     * Unsubscribe all
     * @return void
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     * @throws \Exception
     */
    public function unsubscribeAll(): void
    {
        $actor = $this->getName();

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);
        $rpcProxy->unsubscribeAll($actor);
    }

    /**
     * Publish subscription
     *
     * @param string $channel
     * @param string $message
     * @param array|null $excludeActorList
     * @return void
     * @throws ActorException
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function publish(string $channel, string $message, ?array $excludeActorList = []): void
    {
        $from = $this->getName();

        if (empty($excludeActorList)) {
            $excludeActorList = [$from];
        }

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);

        $rpcProxy->publish($channel, $message, $excludeActorList, $from);
    }

    /**
     * @param string $channel
     * @param string $message
     * @return void
     * @throws ActorException
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function publishTo(string $channel, string $message): void
    {
        $from = $this->getName();

        $excludeActorList = [$from];

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);
        $rpcProxy->publish($channel, $message, $excludeActorList, $from);
    }

    /**
     * @param string $channel
     * @param string $message
     * @return void
     * @throws ActorException
     * @throws \ESD\Plugins\ProcessRPC\ProcessRPCException
     */
    public function publishIn(string $channel, string $message)
    {
        $from = $this->getName();

        $excludeActorList = [];

        /** @var MulticastChannel $rpcProxy */
        $rpcProxy = $this->callProcessName($this->getMulticastConfig()->getProcessName(), MulticastChannel::class, true);
        $rpcProxy->publish($channel, $message, $excludeActorList, $from);
    }
}
