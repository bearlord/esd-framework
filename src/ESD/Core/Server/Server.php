<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server;

use DI\Container;
use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;
use ESD\Core\Context\ContextManager;
use ESD\Core\DI\DI;
use ESD\Core\Log\Log;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Config\ConfigContext;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Plugins\Config\ConfigPlugin;
use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Plugins\Event\EventPlugin;
use ESD\Core\Plugins\Logger\LoggerPlugin;
use ESD\Core\Server\Beans\ClientInfo;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\RequestProxy;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\ResponseProxy;
use ESD\Core\Server\Beans\ServerStats;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Port\PortManager;
use ESD\Core\Server\Port\ServerPort;
use ESD\Core\Server\Process\ManagerProcess;
use ESD\Core\Server\Process\MasterProcess;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Process\ProcessManager;
use Psr\Log\LoggerInterface;

/**
 * Class Server
 * @package ESD\Core\Server
 */
abstract class Server
{
    /**
     * @var Server
     */
    public static $instance;

    /**
     * Whether to start
     * @var bool
     */
    public static $isStart = false;

    /**
     * server configuration
     * @var ServerConfig
     */
    protected $serverConfig;

    /**
     * Swoole server
     * @var \Swoole\WebSocket\Server
     */
    protected $server;

    /**
     * Server port
     * @var ServerPort
     */
    private $mainPort;

    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * @var PortManager
     */
    protected $portManager;

    /**
     * @var PluginInterfaceManager
     */
    protected $plugManager;

    /**
     * @var PluginInterfaceManager
     */
    protected $basePlugManager;

    /**
     * Is it configured
     * @var bool
     */
    private $configured = false;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Server constructor.
     *
     * @param ServerConfig $serverConfig
     * @param string $defaultPortClass
     * @param string $defaultProcessClass
     * @throws \ESD\Core\Exception
     * @throws \Exception
     */
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        self::$instance = $this;
        $this->serverConfig = $serverConfig;

        //Get DI container
        $this->container = DI::getInstance()->getContainer();

        //Set the default Log
        DISet(LoggerInterface::class, new Log());
        $this->container->set(Server::class, $this);
        $this->container->set(ServerConfig::class, $this->serverConfig);

        //Set time zone
        $this->setTimeZone($this->serverConfig);

        //Register the Process's ContextBuilder
        $contextBuilder = ContextManager::getInstance()->getContextBuilder(ContextBuilder::SERVER_CONTEXT,
            function () {
                return new ServerContextBuilder($this);
            });
        $this->context = $contextBuilder->build();
        //-------------------------------------------------------------------------------------
        $this->portManager = new PortManager($this, $defaultPortClass);
        $this->processManager = new ProcessManager($this, $defaultProcessClass);
        $this->basePlugManager = new PluginInterfaceManager($this);

        //Print banner
        printf("%s\n", $serverConfig->getBanner());

        //Initialize the default plugin and add the Config/Logger/Event plugin
        $this->basePlugManager->addPlug(new ConfigPlugin());
        $this->basePlugManager->addPlug(new LoggerPlugin());
        $this->basePlugManager->addPlug(new EventPlugin());
        $this->basePlugManager->order();
        $this->basePlugManager->init($this->context);
        $this->basePlugManager->beforeServerStart($this->context);

        //Merge ServerConfig configuration
        $this->serverConfig->merge();

        //Configure the DI container
        $this->container->set(Response::class, new ResponseProxy());
        $this->container->set(Request::class, new RequestProxy());
        set_exception_handler(function ($e) {
            $this->getLog()->error($e);
        });

        //Only get the above to initialize the plugManager
        $this->plugManager = new PluginInterfaceManager($this);
        $this->container->set(PluginInterfaceManager::class, $this->getPlugManager());
    }

    /**
     * Add a port instance and a class to initialize the instance through configuration
     *
     * @param string $name
     * @param PortConfig $portConfig
     * @param null $portClass
     * @throws ConfigException
     */
    public function addPort(string $name, PortConfig $portConfig, $portClass = null)
    {
        if ($this->isConfigured()) {
            throw new ConfigException("Configuration is locked, please add before calling configure");
        }
        $this->portManager->addPortConfig($name, $portConfig, $portClass);
    }

    /**
     * Add process
     *
     * @param string $name
     * @param null $processClass
     * @param string $groupName
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function addProcess(string $name, $processClass = null, string $groupName = Process::DEFAULT_GROUP)
    {
        if ($this->isConfigured()) {
            throw new ConfigException("Configuration is locked, please add before calling configure");
        }
        $this->processManager->addCustomProcessesConfig($name, $processClass, $groupName);
    }

    /**
     * Adding plugins and adding configuration can only occur before configure
     *
     * @throws ConfigException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function configure()
    {
        //First generate partial configuration
        $this->getPortManager()->mergeConfig();
        $this->getProcessManager()->mergeConfig();

        //Plugin ordering is not allowed at this time
        $this->plugManager->order();
        $this->plugManager->init($this->context);
        $this->pluginInitialized();

        //Call beforeServerStart of all plugins
        $this->plugManager->beforeServerStart($this->context);

        //Lock configuration
        $this->setConfigured(true);

        //Setting up the main process
        $managerProcess = new ManagerProcess($this);
        $masterProcess = new MasterProcess($this);
        $this->processManager->setMasterProcess($masterProcess);
        $this->processManager->setManagerProcess($managerProcess);

        //Set process name
        Process::setProcessTitle($this->serverConfig->getName());

        //Create a port instance
        $this->getPortManager()->createPorts();

        //Main port
        if ($this->portManager->hasWebSocketPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isWebSocket()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\WebSocket\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        } else if ($this->portManager->hasHttpPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isHttp()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\Http\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        } else {
            $this->mainPort = array_values($this->getPortManager()->getPorts())[0];
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        }
        $portConfigData = $this->mainPort->getPortConfig()->buildConfig();
        $serverConfigData = $this->serverConfig->buildConfig();
        $serverConfigData = array_merge($portConfigData, $serverConfigData);
        $this->server->set($serverConfigData);

        //Multiple ports
        foreach ($this->portManager->getPorts() as $serverPort) {
            $serverPort->create();
        }

        //Configure callback
        $this->server->on("start", [$this, "_onStart"]);
        $this->server->on("shutdown", [$this, "_onShutdown"]);
        $this->server->on("workerError", [$this, "_onWorkerError"]);
        $this->server->on("workerExit", [$this, "_onWorkerExit"]);
        $this->server->on("managerStart", [$this, "_onManagerStart"]);
        $this->server->on("managerStop", [$this, "_onManagerStop"]);
        $this->server->on("workerStart", [$this, "_onWorkerStart"]);
        $this->server->on("pipeMessage", [$this, "_onPipeMessage"]);
        $this->server->on("workerStop", [$this, "_onWorkerStop"]);

        //Configuration process
        $this->processManager->createProcess();
        $this->configureReady();
    }

    /**
     * Plugin initialization is complete
     * @return mixed
     */
    abstract public function pluginInitialized();

    /**
     * All configuration plugins have been initialized
     * @return mixed
     */
    abstract public function configureReady();

    /**
     * On start
     */
    public function _onStart()
    {
        Server::$isStart = true;
        //Send Application Starting Event
        $this->getEventDispatcher()->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationStartingEvent, $this));
        $this->processManager->getMasterProcess()->onProcessStart();
        try {
            $this->onStart();
        } catch (\Throwable $e) {
            $this->getLog()->error($e);
        }
    }

    /**
     * On shutdown
     */
    public function _onShutdown()
    {
        //Send Application Shutdown Event
        $this->getEventDispatcher()->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationShutdownEvent, $this));
        try {
            $this->onShutdown();
        } catch (\Throwable $e) {
            $this->getLog()->error($e);
        }
    }

    /**
     * On worker error
     *
     * @param $serv
     * @param int $worker_id
     * @param int $worker_pid
     * @param int $exit_code
     * @param int $signal
     * @throws \Exception
     */
    public function _onWorkerError($serv, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $this->getLog()->alert("workerId:$worker_id exitCode:$exit_code signal:$signal");
        try {
            $this->onWorkerError($process, $exit_code, $signal);
        } catch (\Throwable $e) {
            $this->getLog()->error($e);
        }
    }

    /**
     * On worker exit
     *
     * @param $serv
     * @param int $worker_id
     * @return bool
     */
    public function _onWorkerExit($serv, int $worker_id)
    {
        return true;
        /*
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStop();
        */
    }

    /**
     * On manager start
     *
     * @throws \Exception
     */
    public function _onManagerStart()
    {
        Server::$isStart = true;
        $this->processManager->getManagerProcess()->onProcessStart();
        try {
            $this->onManagerStart();
        } catch (\Throwable $e) {
            $this->getLog()->error($e);
        }
    }

    /**
     * On manager stop
     *
     * @throws \Exception
     */
    public function _onManagerStop()
    {
        $this->processManager->getManagerProcess()->onProcessStop();
        try {
            $this->onManagerStop();
        } catch (\Throwable $e) {
            $this->getLog()->error($e);
        }
    }

    /**
     * On worker start
     *
     * @param $server
     * @param int $worker_id
     */
    public function _onWorkerStart($server, int $worker_id)
    {
        Server::$isStart = true;
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStart();
    }

    /**
     * On pipe message
     *
     * @param $server
     * @param int $srcWorkerId
     * @param $message
     */
    public function _onPipeMessage($server, int $srcWorkerId, $message)
    {
        $this->processManager->getCurrentProcess()->_onPipeMessage($message, $this->processManager->getProcessFromId($srcWorkerId));
    }

    /**
     * On worker stop
     *
     * @param $server
     * @param int $worker_id
     */
    public function _onWorkerStop($server, int $worker_id)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStop();
    }


    public abstract function onStart();

    public abstract function onShutdown();

    public abstract function onWorkerError(Process $process, int $exit_code, int $signal);

    public abstract function onManagerStart();

    public abstract function onManagerStop();

    /**
     * Start service
     *
     * @throws \Exception
     */
    public function start()
    {
        if ($this->server == null) {
            throw new \Exception("Please call configure first");
        }
        $this->server->start();
    }


    /**
     * Get server
     * @return \Swoole\WebSocket\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get main port
     *
     * @return mixed
     */
    public function getMainPort()
    {
        return $this->mainPort;
    }


    /**
     * Get connections
     *
     * @return \Iterator
     */
    public function getConnections(): \Iterator
    {
        return $this->server->connections;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured(bool $configured): void
    {
        $this->configured = $configured;
    }

    /**
     * Get client info
     *
     * @param int $fd
     * @return ClientInfo
     */
    public function getClientInfo(int $fd): ClientInfo
    {
        return new ClientInfo($this->server->getClientInfo($fd));
    }

    /** Close fd
     *
     * @param int $fd
     * @param bool $reset
     */
    public function closeFd(int $fd, bool $reset = false)
    {
        $this->server->close($fd, $reset);
    }

    /**
     * Auto send, auto judge whether websocket or tcp
     *
     * @param int $fd
     * @param string $data
     */
    public function autoSend(int $fd, string $data)
    {
        $clientInfo = $this->getClientInfo($fd);
        $port = $this->getPortManager()->getPortFromPortNo($clientInfo->getServerPort());
        if ($this->isEstablished($fd)) {
            $this->wsPush($fd, $data, $port->getPortConfig()->getWsOpcode());
        } else {
            $this->send($fd, $data);
        }
    }

    /**
     * Send data to client
     *
     * @param int $fd
     * @param string $data
     * @param int $serverSocket Need for Unix socket dgram, not needed for tcp
     * @return bool
     */
    public function send(int $fd, string $data, int $serverSocket = -1): bool
    {
        return $this->server->send($fd, $data, $serverSocket);
    }

    /**
     * Send file to client
     *
     * @param int $fd
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @return bool
     */
    public function sendFile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->server->sendfile($fd, $filename, $offset, $length);
    }


    /**
     * Send data to udp
     *
     * @param string $ip
     * @param int $port
     * @param string $data
     * @param int $server_socket
     * @return bool
     */
    public function sendToUpd(string $ip, int $port, string $data, int $server_socket = -1): bool
    {
        return $this->server->sendto($ip, $port, $data, $server_socket);
    }

    /**
     * Is exist Fd
     *
     * @param $fd
     * @return bool
     */
    public function existFd($fd): bool
    {
        return $this->server->exist($fd);
    }

    /**
     * Bind fd to uid. set [dispatch_mode=5], all uid connection is allocated to the same worker process
     *
     * @param int $fd
     * @param int $uid
     */
    public function bindUid(int $fd, int $uid)
    {
        $this->server->bind($fd, $uid);
    }

    /**
     * Get Server stats
     *
     * @return ServerStats
     */
    public function stats(): ServerStats
    {
        return new ServerStats($this->server->stats());
    }

    /**
     * Heart beat
     *
     * @param bool $ifCloseConnection
     * @return array
     */
    public function heartbeat(bool $ifCloseConnection = true): array
    {
        return $this->server->heartbeat($ifCloseConnection);
    }


    /**
     * Get Last error
     * 1001 connection is closed by server
     * 1002 connection is closed by client
     * 1003 connection is closing
     * 1004 connection is closed
     * 1005 connection is not exists
     * 1007 receive timeout data
     * 1008 send buffer is full
     * 1202 send data length exceed Server->buffer_output_size setting
     * @return int
     */
    public function getLastError(): int
    {
        return $this->server->getLastError();
    }

    /**
     * Protect connection state, don't be disconnect by heartbeat process
     * @param int $fd
     * @param bool $value
     */
    public function protect(int $fd, bool $value = true)
    {
        $this->server->protect($fd, $value);
    }

    /**
     * Confirm connection, go with enable_delay_receive, don't listen read event, only trigger onConnection callback
     *
     * @param int $fd
     */
    public function confirm(int $fd)
    {
        $this->server->confirm($fd);
    }

    /**
     * Reload all Worker/Task process
     */
    public function reload()
    {
        $this->server->reload();
    }

    /**
     * Shut down server
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

    /**
     * Defer callback function
     *
     * @param callable $callback
     */
    public function defer(callable $callback)
    {
        $this->server->defer($callback);
    }

    /**
     * Websocket push, maximum is 2M
     *
     * @param int $fd
     * @param $data
     * @param int $opcode
     * @param bool $finish
     * @return bool
     */
    public function wsPush(int $fd, $data, int $opcode = 1, bool $finish = true): bool
    {
        return $this->server->push($fd, $data, $opcode, $finish);
    }

    /**
     * Close websocket client connection
     * 
     * @param int $fd
     * @param int $code
     * @param string $reason
     * @return bool
     */
    public function wsDisconnect(int $fd, int $code = 1000, string $reason = ""): bool
    {
        return $this->server->disconnect($fd, $code, $reason);
    }

    /**
     * Is available websocket connection
     *
     * @param int $fd
     * @return bool
     */
    public function isEstablished(int $fd): bool
    {
        if (is_callable([$this->server, "isEstablished"])) {
            return $this->server->isEstablished($fd);
        } else {
            return false;
        }
    }

    /**
     * Pack websocket data
     *
     * @param WebSocketFrame $webSocketFrame
     * @param bool $mask
     * @return string
     */
    public function wsPack(WebSocketFrame $webSocketFrame, $mask = false): string
    {
        return $this->server->pack($webSocketFrame->getData(), $webSocketFrame->getOpcode(), $webSocketFrame->getFinish(), $mask);
    }

    /**
     * Unpack websocket data
     *
     * @param string $data
     * @return WebSocketFrame
     */
    public function wsUnPack(string $data): WebSocketFrame
    {
        return new WebSocketFrame($this->server->unpack($data));
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->processManager;
    }

    /**
     * @return PortManager
     */
    public function getPortManager(): PortManager
    {
        return $this->portManager;
    }

    /**
     * @return PluginInterfaceManager
     */
    public function getPlugManager(): PluginInterfaceManager
    {
        return $this->plugManager;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return EventDispatcher
     * @throws \Exception
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return DIGet(EventDispatcher::class);
    }

    /**
     * @return ServerConfig
     */
    public function getServerConfig(): ServerConfig
    {
        return $this->serverConfig;
    }

    /**
     * @return LoggerInterface
     * @throws \Exception
     */
    public function getLog(): LoggerInterface
    {
        return DIGet(LoggerInterface::class);
    }

    /**
     * @return ConfigContext
     * @throws \Exception
     */
    public function getConfigContext(): ConfigContext
    {
        return DIGet(ConfigContext::class);
    }

    /**
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @return PluginInterfaceManager
     */
    public function getBasePlugManager(): PluginInterfaceManager
    {
        return $this->basePlugManager;
    }

    /**
     * @param ServerConfig $serverConfig
     */
    public function setTimeZone(ServerConfig $serverConfig)
    {
        if (!empty($serverConfig->getTimeZone())) {
            $timeZone = $serverConfig->timeZone;
        } elseif (!empty(ini_get('date.timezone'))) {
            $timeZone = ini_get('date.timezone');
        } else {
            $timeZone = "UTC";
        }
        date_default_timezone_set($timeZone);
    }
}