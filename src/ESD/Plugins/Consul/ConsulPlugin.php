<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
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
    const PROCESS_NAME = "helper";
    const PROCESS_GROUP_NAME = "HelperGroup";

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
        //Requires ActuatorPlugin support, so load after ActuatorPlugin
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
            $pluginInterfaceManager->addPlugin($actuatorPlugin);
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
        //Add a helper process
        Server::$instance->addProcess(self::PROCESS_NAME, HelperConsulProcess::class, self::PROCESS_GROUP_NAME);
        //Auto config
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
            $this->debug(sprintf("Receive Leader changed event: %s", $leaderStatus));
            Leader::$isLeader = $event->isLeader();
        });

        //Each process listens to Service changes
        $call = Server::$instance->getEventDispatcher()->listen(ConsulServiceChangeEvent::ConsulServiceChangeEvent);
        $call->call(function (ConsulServiceChangeEvent $event) {
            $this->debug(sprintf("Receive Service changed event: %s", $event->getConsulServiceListInfo()->getServiceName());
            Services::modifyServices($event);
        });

        //Helper process
        if (Server::$instance->getProcessManager()->getCurrentProcess()->getProcessName() === self::PROCESS_NAME) {
            $this->consul = new Consul($this->consulConfig);
            //Process monitoring server information
            $call = Server::$instance->getEventDispatcher()->listen(ProcessEvent::ProcessStopEvent, null, true);
            $call->call(function () {
                //Synchronous request to release the leader
                $this->consul->releaseLeader(false);
                //Synchronous request to cancel service
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