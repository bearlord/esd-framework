<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Consul;

use ESD\Plugins\Cloud\Consul\Beans\ConsulServiceInfo;
use ESD\Plugins\Cloud\Consul\Beans\ConsulServiceListInfo;
use ESD\Plugins\Cloud\Consul\Config\ConsulConfig;
use ESD\Plugins\Cloud\Consul\Event\ConsulAddServiceMonitorEvent;
use ESD\Plugins\Cloud\Consul\Event\ConsulOneServiceChangeEvent;
use ESD\Plugins\Cloud\Consul\Event\ConsulServiceChangeEvent;
use ESD\Psr\Cloud\ServiceInfo;
use ESD\Psr\Cloud\ServiceInfoList;
use ESD\Psr\Cloud\Services;
use ESD\Server\Coroutine\Server;

/**
 * Class Services
 * @package ESD\Plugins\Cloud\Consul
 */
class ConsulServices implements Services
{
    /**
     * @var ConsulConfig
     */
    protected $consulConfig;

    /**
     * @var ConsulServiceInfo[]
     */
    private static $services = [];

    /**
     * ConsulServices constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->consulConfig = DIGet(ConsulConfig::class);
    }

    /**
     * Modify services
     * @param ConsulServiceChangeEvent $consulServiceChangeEvent
     * @throws \Exception
     */
    public function modifyServices(ConsulServiceChangeEvent $consulServiceChangeEvent)
    {
        $this->services[$consulServiceChangeEvent->getConsulServiceListInfo()->getServiceName()]
            = $consulServiceChangeEvent->getConsulServiceListInfo()->getServiceInfos();

        //At the same time, this process triggers a more detailed ConsulServiceChangeEvent event with the service name
        $consulOneServiceChangeEvent = new ConsulOneServiceChangeEvent($consulServiceChangeEvent->getConsulServiceListInfo()->getServiceName(),
            $consulServiceChangeEvent->getConsulServiceListInfo()
        );
        Server::$instance->getEventDispatcher()->dispatchEvent($consulOneServiceChangeEvent);
    }

    /**
     * Get services
     * @param string $service
     * @return ConsulServiceInfo[]
     * @throws \Exception
     */
    public function getServices(string $service): array
    {
        $consulServiceInfos = $this->services[$service] ?? null;

        //There are only two cases where it is null. One is the first acquisition, and the other is the process data is lost after reload.
        if ($consulServiceInfos == null) {
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ConsulAddServiceMonitorEvent($service),
                Server::$instance->getProcessManager()->getProcessFromName(ConsulPlugin::PROCESS_NAME)
            );
            $call = Server::$instance->getEventDispatcher()->listen(ConsulOneServiceChangeEvent::ConsulOneServiceChangeEvent . "::" . $service, null, true);
            /** @var ConsulOneServiceChangeEvent $consulGetServiceEvent */
            $consulGetServiceEvent = $call->wait();
            $consulServiceInfos = $consulGetServiceEvent->getConsulServiceListInfo()->getServiceInfos();
        }

        $serverListQueryTags = $this->consulConfig->getServerListQueryTags();
        $tag = null;
        if ($serverListQueryTags != null) {
            $tag = $serverListQueryTags[$service] ?? $this->consulConfig->getDefaultQueryTag();
        }
        if ($tag != null) {
            foreach ($consulServiceInfos as $key => $value) {
                if (empty($value->getServiceTags())) {
                    unset($consulServiceInfos[$key]);
                } else {
                    if (!in_array($tag, $value->getServiceTags())) {
                        unset($consulServiceInfos[$key]);
                    }
                }
            }
        }

        $serviceInfoList = new ConsulServiceListInfo($service, $consulServiceInfos);
        return $serviceInfoList;
    }

    /**
     * Get service
     * @param string $service
     * @return ServiceInfo|null
     * @throws \Exception
     */
    public function getService(string $service): ?ServiceInfo
    {
        $result = $this->getServices($service);
        if ($result == null || empty($result->getServiceInfos())) return null;
        return $result->getServiceInfos()[array_rand($result->getServiceInfos())];
    }
}