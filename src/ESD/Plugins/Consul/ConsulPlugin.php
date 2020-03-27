<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Process\ProcessEvent;
use ESD\Plugins\Actuator\ActuatorPlugin;
use ESD\Plugins\Consul\Config\ConsulConfig;
use ESD\Plugins\Consul\Event\ConsulLeaderChangeEvent;
use ESD\Plugins\Consul\Event\ConsulServiceChangeEvent;
use ESD\Server\Co\Server;

/**
 * Class ConsulPlugin
 * @package ESD\Plugins\Consul
 */
class ConsulPlugin extends AbstractPlugin
{
    use GetLogger;
    const processName = "helper";
    const processGroupName = "HelperGroup";

    /**
     * @var ConsulConfig
     */
    private $consulConfig;

    /**
     * @var Consul
     */
    private $consul;

    /**
     * ConsulPlugin constructor.
     * @param ConsulConfig $consulConfig
     * @throws \ReflectionException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(ConsulConfig $consulConfig = null)
    {
        parent::__construct();
        //需要ActuatorPlugin的支持，所以放在ActuatorPlugin后加载
        $this->atAfter(ActuatorPlugin::class);
        if ($consulConfig == null) {
            $consulConfig = new ConsulConfig(null);
        }
        $this->consulConfig = $consulConfig;
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Exception
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $actuatorPlugin = $pluginInterfaceManager->getPlug(ActuatorPlugin::class);
        if ($actuatorPlugin == null) {
            $actuatorPlugin = new ActuatorPlugin();
            $pluginInterfaceManager->addPlug($actuatorPlugin);
        }
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Consul";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        //添加一个helper进程
        Server::$instance->addProcess(self::processName, HelperConsulProcess::class, self::processGroupName);
        //自动配置
        $this->consulConfig->autoConfig();
        $this->consulConfig->merge();
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //Each process listens for Leader changes
        $call = Server::$instance->getEventDispatcher()->listen(ConsulLeaderChangeEvent::ConsulLeaderChangeEvent);
        $call->call(function (ConsulLeaderChangeEvent $event) {
            $leaderStatus = $event->isLeader() ? "true" : "false";
            $this->debug("收到Leader变更事件：$leaderStatus");
            Leader::$isLeader = $event->isLeader();
        });

        //Each process listens to Service changes
        $call = Server::$instance->getEventDispatcher()->listen(ConsulServiceChangeEvent::ConsulServiceChangeEvent);
        $call->call(function (ConsulServiceChangeEvent $event) {
            $this->debug("收到Service变更事件：{$event->getConsulServiceListInfo()->getServiceName()}");
            Services::modifyServices($event);
        });

        //Helper process
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::processName) {
            $this->consul = new Consul($this->consulConfig);
            //进程监听关服信息
            $call = Server::$instance->getEventDispatcher()->listen(ProcessEvent::ProcessStopEvent, null, true);
            $call->call(function () {
                //同步请求释放leader，关服操作无法使用协程
                $this->consul->releaseLeader(false);
                //同步请求注销service
                $this->consul->deregisterService(false);
            });
        }
        $this->ready();
    }

    /**
     * @return ConsulConfig
     */
    public function getConsulConfig(): ConsulConfig
    {
        return $this->consulConfig;
    }
}