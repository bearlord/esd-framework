<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Consul;

use ESD\Plugins\Consul\Beans\ConsulServiceInfo;
use ESD\Plugins\Consul\Event\ConsulAddServiceMonitorEvent;
use ESD\Plugins\Consul\Event\ConsulServiceChangeEvent;
use ESD\Server\Co\Server;

/**
 * Class Services
 * @package ESD\Plugins\Consul
 */
class Services
{
    /**
     * @var ConsulServiceInfo[]
     */
    private static $services = [];

    /**
     * 服务变更
     * @param ConsulServiceChangeEvent $consulServiceChangeEvent
     * @throws \Exception
     */
    public static function modifyServices(ConsulServiceChangeEvent $consulServiceChangeEvent)
    {
        self::$services[$consulServiceChangeEvent->getConsulServiceListInfo()->getServiceName()]
            = $consulServiceChangeEvent->getConsulServiceListInfo()->getConsulServiceInfos();

        //At the same time, this process triggers a more detailed ConsulServiceChangeEvent event with the service name
        $consulServiceChangeEvent->setType(
            ConsulServiceChangeEvent::ConsulServiceChangeEvent . "::" . $consulServiceChangeEvent->getConsulServiceListInfo()->getServiceName());
        Server::$instance->getEventDispatcher()->dispatchEvent($consulServiceChangeEvent);
    }

    /**
     * 获取服务
     * @param string $service
     * @return ConsulServiceInfo[]
     * @throws \Exception
     */
    public static function getServices(string $service): array
    {
        $consulServiceInfos = self::$services[$service] ?? null;
        //There are only two cases where it is null. One is the first acquisition, and the other is the process data is lost after reload.
        if ($consulServiceInfos == null) {
            Server::$instance->getEventDispatcher()->dispatchProcessEvent(
                new ConsulAddServiceMonitorEvent($service),
                Server::$instance->getProcessManager()->getProcessFromName(ConsulPlugin::processName)
            );
            $call = Server::$instance->getEventDispatcher()->listen(ConsulServiceChangeEvent::ConsulServiceChangeEvent . "::" . $service, null, true);
            $consulGetServiceEvent = $call->wait();
            if ($consulGetServiceEvent instanceof ConsulServiceChangeEvent) {
                $consulServiceInfos = $consulGetServiceEvent->getConsulServiceListInfo()->getConsulServiceInfos();
            }
        }

        $consulPlugin = Server::$instance->getPlugManager()->getPlug(ConsulPlugin::class);
        if ($consulPlugin instanceof ConsulPlugin) {
            $consulConfig = $consulPlugin->getConsulConfig();
            $serverListQueryTags = $consulConfig->getServerListQueryTags();
            $tag = null;
            if ($serverListQueryTags != null) {
                $tag = $serverListQueryTags[$service] ?? $consulConfig->getDefaultQueryTag();
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
        }
        return $consulServiceInfos;
    }
}