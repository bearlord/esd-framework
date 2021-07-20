<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;
use ESD\Core\Context\ContextManager;
use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;
use Psr\Log\LoggerInterface;

/**
 * Class Process
 * @package ESD\Core\Server\process
 */
abstract class Process
{
    const DEFAULT_GROUP = "DefaultGroup";

    const WORKER_GROUP = "WorkerGroup";

    const SERVER_GROUP = "ServerGroup";

    const SOCK_DGRAM = 2;

    const PROCESS_TYPE_WORKER = 1;

    const PROCESS_TYPE_CUSTOM = 3;

    /**
     * Process type
     * @var int
     */
    protected $processType;


    /**
     * Process id
     * @var int
     */
    protected $processId;

    /**
     * Process pid
     * @var int
     */
    protected $processPid;

    /**
     * Process name
     * @var string
     */
    protected $processName;

    /**
     * @var Server
     */
    protected $server;

    /**
     * 进程组名
     * @var string
     */
    protected $groupName;

    /**
     * Swoole process
     * @var \Swoole\Process
     */
    protected $swooleProcess;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Swoole\Coroutine\Socket
     */
    private $socket;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var bool
     */
    protected $isReady = false;

    /**
     * Channel[]
     * @var array
     */
    protected $waitChannel = [];

    /**
     * Process constructor.
     * @param Server $server
     * @param int $processId
     * @param string $name
     * @param string $groupName
     */
    public function __construct(Server $server, int $processId, string $name = null, string $groupName = self::DEFAULT_GROUP)
    {
        $this->server = $server;
        $this->groupName = $groupName;
        $this->processId = $processId;
        if ($groupName == self::WORKER_GROUP) {
            $this->processType = self::PROCESS_TYPE_WORKER;
        } else {
            $this->processType = self::PROCESS_TYPE_CUSTOM;
        }
        $this->processName = $name;

        $contextBuilder = ContextManager::getInstance()->getContextBuilder(ContextBuilder::PROCESS_CONTEXT,
            function () {
                return new ProcessContextBuilder($this);
            });
        $this->context = $contextBuilder->build();
    }

    /**
     * @param int $SIG
     * @param callable $param
     */
    public static function signal(int $SIG, callable $param)
    {
        \Swoole\Process::signal($SIG, $param);
    }

    /**
     * Create process
     *
     * @return Process
     */
    public function createProcess(): Process
    {
        $this->swooleProcess = new \Swoole\Process([$this, "_onProcessStart"], false, self::SOCK_DGRAM, true);

        return $this;
    }

    /**
     * Get swoole process
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess(): \Swoole\Process
    {
        return $this->swooleProcess;
    }

    /**
     * Get process name
     *
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName;
    }

    /**
     * Get server
     *
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * Get group name
     *
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * Get context
     *
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->isReady;
    }

    /**
     * @param bool $isReady
     */
    public function setIsReady(bool $isReady): void
    {
        $this->isReady = $isReady;
        foreach ($this->waitChannel as $channel) {
            $channel->close();
        }
        $this->waitChannel = [];
    }

    /**
     * Execute external command.
     *
     * @param $path
     * @param $params
     */
    protected function exec($path, $params)
    {
        $this->swooleProcess->exec($path, $params);
    }

    /**
     * Set process name
     *
     * @param $name
     */
    protected function setName($name)
    {
        $this->processName = $name;
        self::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $name);
    }

    /**
     * 进程启动的回调
     */
    public function _onProcessStart()
    {
        $this->log = Server::$instance->getLog();
        $this->eventDispatcher = Server::$instance->getEventDispatcher();
        try {
            Server::$isStart = true;
            if ($this->processName != null) {
                $this->setName($this->processName);
            }
            $this->server->getProcessManager()->setCurrentProcessId($this->processId);
            $this->processPid = getmypid();
            $this->server->getProcessManager()->setCurrentProcessPid($this->processPid);

            //Basic plugin initialization
            $this->server->getBasePlugManager()->beforeProcessStart($this->context);
            $this->server->getBasePlugManager()->waitReady();

            //User plugin initialization
            $this->server->getPlugManager()->beforeProcessStart($this->context);
            $this->server->getPlugManager()->waitReady();
            $this->setIsReady(true);
            $this->init();

            /*
            $this->log->info(Yii::t('esd', 'Process ready'));
            */

            if ($this->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
                $this->getProcessManager()->setCurrentProcessId($this->processId);
                Process::signal(SIGTERM, [$this, '_onProcessStop']);
                $this->socket = $this->swooleProcess->exportSocket();
                \Swoole\Coroutine::create(function () {
                    while (true) {
                        $recv = $this->socket->recv();
                        if (empty($recv)) break;

                        //Get process id
                        $unpackData = unpack("N", $recv);
                        $processId = $unpackData[1];
                        $fromProcess = $this->server->getProcessManager()->getProcessFromId($processId);
                        \Swoole\Coroutine::create(function () use ($recv, $fromProcess) {
                            $this->_onPipeMessage(serverUnSerialize(substr($recv, 4)), $fromProcess);
                        });
                    }
                });
            }

            enableRuntimeCoroutine();

            //Dispatch event
            $this->eventDispatcher->dispatchEvent(new ProcessEvent(ProcessEvent::ProcessStartEvent, $this));
            $this->onProcessStart();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    /**
     * Process start
     *
     * @return mixed
     */
    public abstract function init();

    /**
     * On pipe message
     *
     * @param Message $message
     * @param Process $fromProcess
     */
    public function _onPipeMessage(Message $message, Process $fromProcess)
    {
        $this->waitReady();
        try {
            if (!MessageProcessor::dispatch($message)) {
                $this->onPipeMessage($message, $fromProcess);
            }
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    /**
     * On process stop.
     */
    public function _onProcessStop()
    {
        try {
            //Dispatch event
            $this->eventDispatcher->dispatchEvent(new ProcessEvent(ProcessEvent::ProcessStopEvent, $this));
            $this->onProcessStop();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
        if ($this->swooleProcess != null) {
            $this->swooleProcess->exit(0);
        }
    }

    /**
     * Send message to the process
     *
     * @param Message $message
     * @param Process $toProcess
     */
    public function sendMessage(Message $message, Process $toProcess)
    {
        //If send to self
        if ($this->getProcessId() == $toProcess->getProcessId()) {
            $this->_onPipeMessage($message, $this);
            return;
        }
        if ($toProcess->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
            if (!is_string($message)) {
                $message = serverSerialize($message);
            }

            //Add source process id
            $message = pack("N", $this->getProcessId()) . $message;
            $toProcess->swooleProcess->write($message);
        } else {
            //If process is worker or task
            $this->server->getServer()->sendMessage($message, $toProcess->getProcessId());
        }
    }

    /**
     * Process start
     *
     * @return mixed
     */
    public abstract function onProcessStart();

    /**
     * On process stop
     *
     * @return mixed
     */
    public abstract function onProcessStop();

    /**
     * On pipe message
     *
     * @param Message $message
     * @param Process $fromProcess
     * @return mixed
     */
    public abstract function onPipeMessage(Message $message, Process $fromProcess);

    /**
     * @return int
     */
    public function getProcessType(): int
    {
        return $this->processType;
    }

    /**
     * @return int
     */
    public function getProcessId(): int
    {
        return $this->processId;
    }

    /**
     * @return int
     */
    public function getProcessPid(): int
    {
        return $this->processPid;
    }

    /**
     * Get process manager
     *
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->server->getProcessManager();
    }

    /**
     * Is darwin
     *
     * @return bool
     */
    public static function isDarwin()
    {
        if (PHP_OS == "Darwin") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    public static function setProcessTitle($title)
    {
        if (self::isDarwin()) {
            return;
        }
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        else {
            @swoole_set_process_name($title);
        }
    }

    /**
     * Wait ready
     */
    public function waitReady()
    {
        if ($this->isReady()) {
            return;
        }
        $channel = DIGet(Channel::class);
        $this->waitChannel[] = $channel;
        $channel->pop();
    }
}